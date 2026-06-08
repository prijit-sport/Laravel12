<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StockPrice;
use App\Services\RuleBasedAnalysisService;
use App\Services\StockService;
use App\Services\TechnicalIndicatorService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

final class UsScreenerController extends Controller
{
    private const CACHE_MINUTES = 10;
    private const DEFAULT_LIMIT = 15;


    /**
     * US Screener: search stocks using symbol from DB.
     * - If user enters full symbol (e.g. AAPL) => analyze it directly
     * - If user enters pattern (e.g. A*, *L) => filter symbols existing in DB
     */
    public function index(Request $request)
    {
        $q = strtoupper(trim((string) $request->query('q', '')));
        
        // แก้ไขบรรทัดนี้เพื่อเคลียร์ปัญหา Type Hinting Warning ใน IDE
        $limit = (int) $request->query('limit');
        $limit = $limit > 0 ? $limit : self::DEFAULT_LIMIT;

        $cacheKey = 'us_screener:' . md5($q . ':' . $limit);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return View::make('stock.us_screener', $cached);
        }

        $stockService = app(StockService::class);
        $indicatorService = app(TechnicalIndicatorService::class);
        $ruleBasedService = app(RuleBasedAnalysisService::class);

        $errorMessage = null;
        $items = [];

        // 1) ค้นหาแบบระบุชื่อหุ้นตรงๆ (Direct symbol analyze)
        if ($q !== '' && $this->looksLikeFullSymbol($q)) {
            $analysis = $this->analyzeOne($q, $stockService, $indicatorService, $ruleBasedService);
            if (!($analysis['ok'] ?? false) && isset($analysis['error'])) {
                $errorMessage = "หุ้น {$q}: " . $analysis['error'];
            }
            $items = [$q => $analysis];
        } else {
            // 2) ค้นหาแบบใช้ Pattern หรือเปิดดูหน้าแรกทั้งหมด
            $pattern = $q;
            $symbols = $this->fetchCandidateSymbols($pattern);

            if ($symbols->isEmpty()) {
                $errorMessage = 'ยังไม่มีข้อมูลหุ้นในฐานข้อมูลสำหรับข้อมูลที่คุณค้นหา (กรุณาเปิดหน้า /stock/{symbol} เพื่อดึงข้อมูลหุ้นตัวใหม่เข้าฐานข้อมูลก่อน)';
            }

            $symbols = $symbols->take($limit);

            foreach ($symbols as $symbol) {
                $symbol = (string) $symbol;
                $analysis = $this->analyzeOne($symbol, $stockService, $indicatorService, $ruleBasedService);
                
                // หากติด API Limit ระหว่างรัน Loop ให้หยุดรันตัวถัดไปทันทีเพื่อเซฟระบบ และแจ้งเตือนผู้ใช้
                if (isset($analysis['error']) && str_contains(strtolower($analysis['error']), 'too many requests')) {
                    $errorMessage = 'ระบบเรียกข้อมูลผ่าน API เกินกำหนด (Twelve Data API Limit) กำลังแสดงผลข้อมูลเท่าที่ดึงได้ในปัจจุบัน';
                    $items[$symbol] = $analysis;
                    break;
                }
                
                $items[$symbol] = $analysis;
            }
        }

        $out = [
            'q' => $request->query('q', ''),
            'limit' => $limit,
            'items' => $items,
            'errorMessage' => $errorMessage,
        ];

        // บันทึก Cache ไว้เฉพาะเมื่อไม่มี Error ร้ายแรงเกิดขึ้น
        if ($errorMessage === null || !str_contains(strtolower($errorMessage), 'limit')) {
            Cache::put($cacheKey, $out, now()->addMinutes(self::CACHE_MINUTES));
        }

        return View::make('stock.us_screener', $out);
    }

    /**
     * @return Collection<int,string>
     */
    private function fetchCandidateSymbols(string $pattern): Collection
    {
        $pattern = trim($pattern);

        // ดึงรายชื่อสัญลักษณ์หุ้น US จากตาราง us_stocks (universe/listings)
        $query = UsStock::query()->select('symbol')->distinct();

        if ($pattern === '') {
            return $query->orderBy('symbol')->limit(50)->pluck('symbol');
        }

        // รองรับการค้นหาแบบ Wildcard เช่น A*, *L, A*L
        if (str_contains($pattern, '*')) {
            $like = str_replace('*', '%', $pattern);
            return $query->where('symbol', 'like', $like)->orderBy('symbol')->pluck('symbol');
        }

        // ค้นหาแบบชื่อตรงกันเป๊ะๆ
        return $query->where('symbol', $pattern)->orderBy('symbol')->pluck('symbol');
    }

    private function looksLikeFullSymbol(string $q): bool
    {
        if ($q === '') {
            return false;
        }
        if (str_contains($q, '*')) {
            return false;
        }
        if (preg_match('/\s/', $q) === 1) {
            return false;
        }
        return (bool) preg_match('/^[A-Z0-9._-]{1,20}$/', $q);
    }

    /**
     * @return array{ok: bool, symbol: string, name: ?string, close: ?float, percent_change: ?float, rsi: ?float, sma50: ?float, macd_histogram: ?float, rsi_signal: string, trend_signal: string, macd_signal: string, summary: string, error: ?string}
     */
    private function analyzeOne(
        string $symbol,
        StockService $stockService,
        TechnicalIndicatorService $indicatorService,
        RuleBasedAnalysisService $ruleBasedService
    ): array {
        $symbol = strtoupper(trim($symbol));

        try {
            $timeSeries = $stockService->getTimeSeries($symbol, 200);
            $latestDb = $stockService->getLatestFromDb($symbol);

            $close = ($latestDb['ok'] ?? false) ? ($latestDb['close'] ?? null) : null;
            $percentChange = ($latestDb['ok'] ?? false) ? ($latestDb['percent_change'] ?? null) : null;

            if (($timeSeries['ok'] ?? false) !== true || empty($timeSeries['values'])) {
                return [
                    'ok' => false,
                    'symbol' => $symbol,
                    'name' => null,
                    'close' => null,
                    'percent_change' => null,
                    'rsi' => null,
                    'sma50' => null,
                    'macd_histogram' => null,
                    'rsi_signal' => '-',
                    'trend_signal' => '-',
                    'macd_signal' => '-',
                    'summary' => '-',
                    'error' => (string) ($timeSeries['error'] ?? 'ไม่สามารถดึงข้อมูลอนุกรมเวลาได้'),
                ];
            }

            $closes = array_map(static fn (array $row): float => (float) ($row['close'] ?? 0.0), $timeSeries['values']);

            // คำนวณค่าทาง Technical Indicators
            $rsi = $indicatorService->rsi($closes, 14);
            $sma50 = $indicatorService->sma($closes, 50);
            $macdData = $indicatorService->macd($closes);
            $macdHistogram = $macdData['histogram'] ?? null;

            if ($close === null && !empty($closes)) {
                $close = $closes[count($closes) - 1];
            }

            $rule = $ruleBasedService->analyze([
                'symbol' => $symbol,
                'close' => $close,
                'percent_change' => $percentChange,
                'rsi' => $rsi,
                'sma20' => null,
                'sma50' => $sma50,
                'macd_histogram' => $macdHistogram,
            ]);

            $rsiSignal = $this->formatRsiSignal($rsi);
            $trendSignal = $this->formatTrendSignal($close, $sma50);
            $macdSignal = $this->formatMacdSignal($macdHistogram);

            return [
                'ok' => true,
                'symbol' => $symbol,
                'name' => $timeSeries['name'] ?? null,
                'close' => $close,
                'percent_change' => $percentChange,
                'rsi' => $rsi,
                'sma50' => $sma50,
                'macd_histogram' => $macdHistogram,
                'rsi_signal' => $rsiSignal,
                'trend_signal' => $trendSignal,
                'macd_signal' => $macdSignal,
                'summary' => ($rule['ok'] ?? false) ? ($rule['summary'] ?? '-') : '-',
                'error' => null,
            ];

        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'symbol' => $symbol,
                'name' => null,
                'close' => null,
                'percent_change' => null,
                'rsi' => null,
                'sma50' => null,
                'macd_histogram' => null,
                'rsi_signal' => '-',
                'trend_signal' => '-',
                'macd_signal' => '-',
                'summary' => '-',
                'error' => 'ระบบภายในขัดข้อง: ' . $e->getMessage(),
            ];
        }
    }

    private function formatRsiSignal(?float $rsi): string
    {
        if ($rsi === null) {
            return '-';
        }
        if ($rsi > 70.0) {
            return 'Overbought';
        }
        if ($rsi < 30.0) {
            return 'Oversold';
        }
        return 'Neutral';
    }

    private function formatTrendSignal(?float $close, ?float $sma50): string
    {
        if ($close === null || $sma50 === null) {
            return '-';
        }
        if ($close > $sma50) {
            return 'ขาขึ้น (Above SMA50)';
        }
        if ($close < $sma50) {
            return 'ขาลง (Below SMA50)';
        }
        return 'Sideways';
    }

    private function formatMacdSignal(?float $hist): string
    {
        if ($hist === null) {
            return '-';
        }
        if ($hist > 0.0) {
            return 'Bullish';
        }
        if ($hist < 0.0) {
            return 'Bearish';
        }
        return 'Neutral';
    }
}