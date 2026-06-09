<?php
 
declare(strict_types=1);
 
namespace App\Http\Controllers;
 
use App\Models\StockPrice;
use App\Models\Watchlist;
use App\Services\TechnicalIndicatorService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
 
final class DashboardController extends Controller
{
    public function index(): View
    {
        $totalSymbols   = $this->countDistinctSymbolsFromDb();
        $watchlistItems = $this->getWatchlistItems();
        $watchlistCount = $watchlistItems->count();
 
        $indicatorService = app(TechnicalIndicatorService::class);
 
        $bullishCount  = 0;
        $bearishCount  = 0;
        $oversoldCount = 0;
 
        /** @var array<string, array<string, mixed>> $watchlistData */
        $watchlistData = [];
 
        foreach ($watchlistItems as $item) {
            /** @var Watchlist $item */
            $symbol = strtoupper(trim((string) $item->symbol));
            if ($symbol === '') {
                continue;
            }
 
            $latest     = $this->getLatestFromDb($symbol);
            $timeSeries = $this->getTimeSeriesFromDb($symbol, 200);
 
            $rsi          = null;
            $sma50        = null;
            $macdHistogram = null;
            $trendSignal  = 'N/A';
            $macdSignal   = 'N/A';
            $rsiSignal    = 'N/A';
 
            if ($timeSeries !== []) {
                $closes = array_map(
                    static fn (array $row): float => (float) ($row['close'] ?? 0.0),
                    $timeSeries
                );
 
                $rsi          = $indicatorService->rsi($closes, 14);
                $sma50        = $indicatorService->sma($closes, 50);
                $macdData     = $indicatorService->macd($closes);
                $macdHistogram = $macdData['histogram'] ?? null;
 
                $closeLatest = $latest['close'] ?? null;
 
                if ($closeLatest !== null && $sma50 !== null) {
                    $trendSignal = ((float) $closeLatest > (float) $sma50) ? 'ขาขึ้น' : 'ขาลง';
                }
 
                if ($macdHistogram !== null) {
                    $macdSignal = ((float) $macdHistogram > 0.0) ? 'Bullish' : 'Bearish';
                }
 
                if ($rsi !== null) {
                    if ((float) $rsi < 30.0) {
                        $rsiSignal = 'Oversold';
                    } elseif ((float) $rsi > 70.0) {
                        $rsiSignal = 'Overbought';
                    } else {
                        $rsiSignal = 'Neutral';
                    }
                }
            }
 
            if ($trendSignal === 'ขาขึ้น') {
                $bullishCount++;
            } elseif ($trendSignal === 'ขาลง') {
                $bearishCount++;
            }
 
            if ($rsi !== null && (float) $rsi < 30.0) {
                $oversoldCount++;
            }
 
            $logoUrl    = Cache::get("logo:{$symbol}");
            $logoUrlOut = is_string($logoUrl) ? $logoUrl : null;
 
            $watchlistData[$symbol] = [
                'symbol'         => $symbol,
                'note'           => $item->note ?? '',
                'logoUrl'        => $logoUrlOut,
                'close'          => $latest['close'] ?? null,
                'percent_change' => $latest['percent_change'] ?? null,
                'rsi'            => $rsi === null ? null : round((float) $rsi, 2),
                'trend_signal'   => $trendSignal,
                'macd_signal'    => $macdSignal,
                'rsi_signal'     => $rsiSignal,
            ];
        }
 
        return view('dashboard.dashboard', [
            'totalSymbols'   => $totalSymbols,
            'watchlistCount' => $watchlistCount,
            'bullishCount'   => $bullishCount,
            'bearishCount'   => $bearishCount,
            'oversoldCount'  => $oversoldCount,
            'watchlistData'  => $watchlistData,
            'watchlistItems' => $watchlistItems,
        ]);
    }
 
    private function countDistinctSymbolsFromDb(): int
    {
        return (int) StockPrice::query()->distinct('symbol')->count('symbol');
    }
 
    /**
     * @return Collection<int, Watchlist>
     */
    private function getWatchlistItems(): Collection
    {
        return Watchlist::query()->get();
    }
 
    /**
     * Get latest price and % change for a symbol from DB (no API call).
     *
     * @return array{ok: bool, close: ?float, percent_change: ?float}
     */
    private function getLatestFromDb(string $symbol): array
    {
        $rows = StockPrice::query()
            ->where('symbol', $symbol)
            ->orderByDesc('trade_date')
            ->limit(2)
            ->get(['trade_date', 'close']);
 
        if ($rows->isEmpty()) {
            return ['ok' => false, 'close' => null, 'percent_change' => null];
        }
 
        // Use Collection::get() instead of array-index access to avoid PHP0407
        $firstRow  = $rows->get(0);
        $secondRow = $rows->get(1);
 
        if ($firstRow === null || !is_finite((float) $firstRow->close)) {
            return ['ok' => false, 'close' => null, 'percent_change' => null];
        }
 
        $closeLatest = (float) $firstRow->close;
 
        if ($secondRow === null) {
            return ['ok' => true, 'close' => $closeLatest, 'percent_change' => null];
        }
 
        $closePrev = (float) $secondRow->close;
        if (!is_finite($closePrev) || $closePrev == 0.0) {
            return ['ok' => true, 'close' => $closeLatest, 'percent_change' => null];
        }
 
        $percentChange = ($closeLatest - $closePrev) / $closePrev * 100.0;
        if (!is_finite($percentChange)) {
            return ['ok' => true, 'close' => $closeLatest, 'percent_change' => null];
        }
 
        return ['ok' => true, 'close' => $closeLatest, 'percent_change' => $percentChange];
    }
 
    /**
     * Fetch DB-only time series (oldest → newest) for indicator computation.
     *
     * @return array<int, array{datetime: string, close: float}>
     */
    private function getTimeSeriesFromDb(string $symbol, int $outputSize): array
    {
        $rows = StockPrice::query()
            ->where('symbol', $symbol)
            ->orderByDesc('trade_date')
            ->limit($outputSize)
            ->get(['trade_date', 'close']);
 
        if ($rows->isEmpty()) {
            return [];
        }
 
        // Reverse so oldest → newest for indicator calculations
        $out = [];
        foreach ($rows->reverse() as $row) {
            /** @var StockPrice $row */
            $out[] = [
                'datetime' => (string) $row->trade_date->toDateString(),
                'close'    => (float) $row->close,
            ];
        }
 
        return $out;
    }
}