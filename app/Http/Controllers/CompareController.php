<?php

namespace App\Http\Controllers;

use App\Services\RuleBasedAnalysisService;
use App\Services\StockService;
use App\Services\TechnicalIndicatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

use Illuminate\Support\Facades\Http; // ไม่ใช้งานใน compare main flow แต่คงไว้เพื่อไม่ให้โค้ดเดิมพัง

final class CompareController
{
    private const MAX_SYMBOLS = 6;
    private const CACHE_MINUTES = 10;

    /**
     * @return \Illuminate\View\View
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $symbolsInput = (string) ($request->query('symbols', 'NVDA,TSM,MU,VRT,AVGO'));
        $symbolsRaw = array_map(static fn (string $s): string => strtoupper(trim($s)), explode(',', $symbolsInput));
        $symbolsRaw = array_filter($symbolsRaw, static fn (string $s): bool => $s !== '');
        $symbolsRaw = array_slice($symbolsRaw, 0, self::MAX_SYMBOLS);
        $symbols = array_values($symbolsRaw);

        $cacheKey = 'compare_' . md5(implode(',', $symbols));

        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            return View::make('stock.compare', [
                'symbols' => $symbols,
                'comparison' => $cachedData['comparison'],
                'userSymbolsInput' => $symbolsInput,
                'errorMessage' => $cachedData['errorMessage'] ?? null,
            ]);
        }

        $stockService = app(StockService::class);
        $indicatorService = app(TechnicalIndicatorService::class);
        $ruleBasedService = app(RuleBasedAnalysisService::class);

        $comparison = [];
        $errorMessage = null;

        foreach ($symbols as $symbol) {
            // Ensure DB is populated if this symbol hasn't existed before
            $timeSeries = $stockService->getTimeSeries($symbol, 200);

            $latestDb = $stockService->getLatestFromDb($symbol);

            $close = $latestDb['ok'] ? ($latestDb['close'] ?? null) : null;
            $percentChange = $latestDb['ok'] ? ($latestDb['percent_change'] ?? null) : null;
            $name = null;

            if (($latestDb['ok'] ?? false) !== true && ($timeSeries['ok'] ?? false) !== true) {
                $comparison[$symbol] = [
                    'ok' => false,
                    'error' => 'ไม่สามารถดึงข้อมูลได้',
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
                ];
                continue;
            }


            $rsi = null;
            $sma50 = null;
            $macdHistogram = null;
            $ruleSummary = '-';

            if (($timeSeries['ok'] ?? false) === true) {
                $closes = array_map(static fn (array $row): float => (float) ($row['close'] ?? 0.0), $timeSeries['values']);

                $rsi = $indicatorService->rsi($closes, 14);
                $sma50 = $indicatorService->sma($closes, 50);
                $macdData = $indicatorService->macd($closes);
                $macdHistogram = $macdData['histogram'] ?? null;

                $stockData = [
                    'symbol' => $symbol,
                    'name' => $name,
                    'close' => $close,
                    'percent_change' => $percentChange,
                    'rsi' => $rsi,
                    'sma20' => null,
                    'sma50' => $sma50,
                    'macd_histogram' => $macdHistogram,
                ];

                $ruleAnalysis = $ruleBasedService->analyze($stockData);
                $ruleSummary = ($ruleAnalysis['ok'] ?? false) === true ? ($ruleAnalysis['summary'] ?? '-') : '-';
            }

            $rsiSignal = $this->getRsiSignal($rsi);
            $trendSignal = $this->getTrendSignal($close, $sma50);
            $macdSignal = $this->getMacdSignal($macdHistogram);

            $comparison[$symbol] = [
                'ok' => true,
                'error' => null,
                'name' => $name,
                'close' => $close,
                'percent_change' => $percentChange,

                'rsi' => $rsi,
                'sma50' => $sma50,
                'macd_histogram' => $macdHistogram,
                'rsi_signal' => $rsiSignal['label'],
                'rsi_color' => $rsiSignal['color'],
                'trend_signal' => $trendSignal['label'],
                'trend_color' => $trendSignal['color'],
                'macd_signal' => $macdSignal['label'],
                'macd_color' => $macdSignal['color'],
                'summary' => $ruleSummary,
            ];
        }

        Cache::put($cacheKey, [
            'comparison' => $comparison,
            'errorMessage' => $errorMessage,
        ], now()->addMinutes(self::CACHE_MINUTES));

        return View::make('stock.compare', [
            'symbols' => $symbols,
            'comparison' => $comparison,
            'userSymbolsInput' => $symbolsInput,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * @param list<string> $symbols
     * @return array<string, array{ok: bool, name: ?string, close: ?float, percent_change: ?float, error: ?string}>
     */
    private function fetchMultipleQuotes(array $symbols): array
    {
        // Deprecated: compare main flow uses DB (getLatestFromDb) to avoid Twelve Data quote rate limit.

        if (empty($symbols)) {
            return [];
        }

        $symbolString = implode(',', $symbols);
        $apiKey = (string) env('TWELVEDATA_API_KEY', '');

        if ($apiKey === '') {
            return array_fill_keys($symbols, [
                'ok' => false,
                'name' => null,
                'close' => null,
                'percent_change' => null,
                'error' => 'API key not configured',
            ]);
        }

        try {
            $response = Http::withoutVerifying()->get('https://api.twelvedata.com/quote', [
                'symbol' => $symbolString,
                'apikey' => $apiKey,
                'format' => 'JSON',
            ]);

            $data = $response->json();

            if (!is_array($data)) {
                return array_fill_keys($symbols, [
                    'ok' => false,
                    'name' => null,
                    'close' => null,
                    'percent_change' => null,
                    'error' => 'Invalid API response',
                ]);
            }

            $result = [];
            foreach ($symbols as $symbol) {
                $quoteData = $data[$symbol] ?? null;

                if ($quoteData === null || !is_array($quoteData)) {
                    $result[$symbol] = [
                        'ok' => false,
                        'name' => null,
                        'close' => null,
                        'percent_change' => null,
                        'error' => 'Symbol not found or API error',
                    ];
                    continue;
                }

                $result[$symbol] = [
                    'ok' => true,
                    'name' => (string) ($quoteData['name'] ?? ''),
                    'close' => $this->parseFloat($quoteData['close'] ?? null),
                    'percent_change' => $this->parseFloat($quoteData['percent_change'] ?? null),
                    'error' => null,
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            return array_fill_keys($symbols, [
                'ok' => false,
                'name' => null,
                'close' => null,
                'percent_change' => null,
                'error' => 'API request failed: ' . $e->getMessage(),
            ]);
        }
    }

    private function parseFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * @return array{label: string, color: string}
     */
    private function getRsiSignal(?float $rsi): array
    {
        if ($rsi === null) {
            return ['label' => '-', 'color' => 'secondary'];
        }

        if ($rsi > 70.0) {
            return ['label' => 'ซื้อมากเกินไป', 'color' => 'danger'];
        }

        if ($rsi < 30.0) {
            return ['label' => 'ขายมากเกินไป', 'color' => 'success'];
        }

        return ['label' => 'ปกติ', 'color' => 'secondary'];
    }

    /**
     * @return array{label: string, color: string}
     */
    private function getTrendSignal(?float $close, ?float $sma50): array
    {
        if ($close === null || $sma50 === null) {
            return ['label' => '-', 'color' => 'secondary'];
        }

        if ($close > $sma50) {
            return ['label' => 'ขาขึ้น', 'color' => 'success'];
        }

        if ($close < $sma50) {
            return ['label' => 'ขาลง', 'color' => 'danger'];
        }

        return ['label' => 'กำลังจุด', 'color' => 'secondary'];
    }

    /**
     * @return array{label: string, color: string}
     */
    private function getMacdSignal(?float $macdHistogram): array
    {
        if ($macdHistogram === null) {
            return ['label' => '-', 'color' => 'secondary'];
        }

        if ($macdHistogram > 0.0) {
            return ['label' => 'Bullish', 'color' => 'success'];
        }

        if ($macdHistogram < 0.0) {
            return ['label' => 'Bearish', 'color' => 'danger'];
        }

        return ['label' => 'กลาง', 'color' => 'secondary'];
    }
}
