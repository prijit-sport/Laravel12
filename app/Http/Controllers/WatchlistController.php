<?php
 
declare(strict_types=1);
 
namespace App\Http\Controllers;
 
use App\Models\Watchlist;
use App\Services\RuleBasedAnalysisService;
use App\Services\StockService;
use App\Services\TechnicalIndicatorService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
 
final class WatchlistController extends Controller
{
    private const TIME_SERIES_SIZE = 200;
    private const CACHE_MINUTES = 10;
 
    /**
     * Display watchlist with stock data and indicators.
     */
    public function index(): View
    {
        $watchlistItems = Watchlist::all();
 
        if ($watchlistItems->isEmpty()) {
            return view('stock.watchlist', ['items' => $watchlistItems, 'data' => []]);
        }
 
        $symbols = $watchlistItems->pluck('symbol')->toArray();
        $data = $this->fetchWatchlistData($symbols, $watchlistItems);
 
        return view('stock.watchlist', ['items' => $watchlistItems, 'data' => $data]);
    }
 
    /**
     * Store a new watchlist item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:20',
            'note' => 'nullable|string|max:255',
        ]);
 
        $symbol = strtoupper(trim((string) ($validated['symbol'] ?? '')));
 
        if ($symbol === '') {
            return redirect('/watchlist')->with('warning', 'ไม่พบสัญลักษณ์หุ้น');
        }
 
        if (Watchlist::where('symbol', $symbol)->exists()) {
            return redirect('/watchlist')->with('warning', "หุ้น {$symbol} มีอยู่ในรายการแล้ว");
        }
 
        Watchlist::create([
            'symbol' => $symbol,
            'note' => $validated['note'] ?? null,
        ]);
 
        return redirect('/watchlist')->with('success', "เพิ่ม {$symbol} เข้า Watchlist เรียบร้อย");
    }
 
    /**
     * Delete a watchlist item.
     */
    public function destroy(int $id): RedirectResponse
    {
        $item = Watchlist::findOrFail($id);
        $symbol = (string) $item->symbol;
        $item->delete();
 
        return redirect('/watchlist')->with('success', "ลบ {$symbol} จาก Watchlist เรียบร้อย");
    }
 
    /**
     * Fetch data for multiple watchlist symbols.
     *
     * @param  list<string>  $symbols
     * @param  Collection  $watchlistItems
     * @return array<string, array<string, mixed>>
     */
    private function fetchWatchlistData(array $symbols, Collection $watchlistItems): array
    {
        $cacheKey = 'watchlist_' . md5(implode(',', $symbols));
 
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
 
        $stockService = new StockService();
        $indicatorService = new TechnicalIndicatorService();
        $ruleBasedService = new RuleBasedAnalysisService();
 
        // Fetch company names via quote API once (only name). Prices/%/indicators come from DB.
        $namesBySymbol = $this->fetchMultipleQuotesForName($symbols);
 
        $data = [];
 
        foreach ($symbols as $symbol) {
            $symbol = strtoupper(trim((string) $symbol));
            if ($symbol === '') {
                continue;
            }
 
            // Populate DB if needed (DB-first; only calls API when missing/stale)
            $timeSeries = $stockService->getTimeSeries($symbol, self::TIME_SERIES_SIZE);
 
            if (($timeSeries['ok'] ?? false) !== true || empty($timeSeries['values'])) {
                $data[$symbol] = [
                    'symbol' => $symbol,
                    'name' => $namesBySymbol[$symbol] ?? '-',
                    'error' => 'ดึงข้อมูลไม่ได้',
                ];
                continue;
            }
 
            $latestDb = $stockService->getLatestFromDb($symbol);
            $close = ($latestDb['ok'] ?? false) ? ($latestDb['close'] ?? null) : null;
            $percentChange = ($latestDb['ok'] ?? false) ? ($latestDb['percent_change'] ?? null) : null;
 
            $closes = array_map(
                static fn (array $row): float => (float) ($row['close'] ?? 0.0),
                $timeSeries['values']
            );
 
            $rsi = $indicatorService->rsi($closes, 14);
            $sma50 = $indicatorService->sma($closes, 50);
 
            $macdData = $indicatorService->macd($closes);
            $macdHistogram = $macdData['histogram'] ?? null;
 
            // Trend signal: close vs SMA50 (same convention as compare page)
            $trendSignal = ($close !== null && $sma50 !== null)
                ? ($close > $sma50 ? 'ขาขึ้น' : 'ขาลง')
                : 'N/A';
 
            // MACD signal
            $macdSignal = $macdHistogram === null
                ? 'N/A'
                : ($macdHistogram > 0.0 ? 'Bullish' : 'Bearish');
 
            $ruleAnalysis = $ruleBasedService->analyze([
                'symbol' => $symbol,
                'name' => $namesBySymbol[$symbol] ?? null,
                'close' => $close,
                'percent_change' => $percentChange,
                'rsi' => $rsi,
                'sma20' => null,
                'sma50' => $sma50,
                'macd_histogram' => $macdHistogram,
            ]);
 
            $data[$symbol] = [
                'symbol' => $symbol,
                'name' => $namesBySymbol[$symbol] ?? '-',
                'close' => $close,
                'percent_change' => $percentChange,
                'rsi' => $rsi === null ? null : round((float) $rsi, 2),
                'trend_signal' => $trendSignal,
                'macd_signal' => $macdSignal,
                'summary' => ($ruleAnalysis['ok'] ?? false) === true ? ($ruleAnalysis['summary'] ?? '-') : '-',
                'note' => $watchlistItems->firstWhere('symbol', $symbol)->note ?? '',
            ];
        }
 
        Cache::put($cacheKey, $data, now()->addMinutes(self::CACHE_MINUTES));
 
        return $data;
    }
 
    /**
     * Fetch company names via Twelve Data quote API (multi-symbol, one call).
     *
     * @param  list<string>  $symbols
     * @return array<string, string>
     */
    private function fetchMultipleQuotesForName(array $symbols): array
    {
        if ($symbols === []) {
            return [];
        }
 
        $apiKey = (string) env('TWELVEDATA_API_KEY', '');
        if ($apiKey === '') {
            return array_fill_keys($symbols, '-');
        }
 
        $symbolString = implode(',', $symbols);
 
        try {
            $response = Http::withoutVerifying()->timeout(15)->get('https://api.twelvedata.com/quote', [
                'symbol' => $symbolString,
                'apikey' => $apiKey,
                'format' => 'JSON',
            ]);
 
            $payload = $response->json();
            if (!is_array($payload)) {
                return array_fill_keys($symbols, '-');
            }
 
            $out = [];
            foreach ($symbols as $symbol) {
                // Single-symbol responses are flat; multi-symbol are keyed by symbol
                $quote = $payload[$symbol] ?? (count($symbols) === 1 ? $payload : null);
                $nameRaw = is_array($quote) ? ($quote['name'] ?? null) : null;
                $out[$symbol] = ($nameRaw === null || $nameRaw === '') ? '-' : (string) $nameRaw;
            }
 
            return $out;
        } catch (\Throwable $e) {
            return array_fill_keys($symbols, '-');
        }
    }
}
