<?php

namespace App\Http\Controllers;

use App\Services\OllamaAnalysisService;

use App\Services\BacktestService;
use App\Services\RuleBasedAnalysisService;
use App\Services\StockService;
use App\Services\TechnicalIndicatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Http\RedirectResponse;

final class StockController extends Controller
{
    public function companyInfo(string $symbol): JsonResponse
    {
        $symbolOut = $this->normalizeSymbol($symbol);

        $service = app(StockService::class);
        $quote = $service->getQuote($symbolOut);

        $name = $quote['name'] ?? null;

        $aiService = app(OllamaAnalysisService::class);
        $result = $aiService->describeCompany($symbolOut, $name);

        return response()->json([
            'ok' => $result['ok'],
            'description' => $result['description'],
            'error' => $result['error'],
        ], 200);
    }

    /**
     * @return \Illuminate\View\View|RedirectResponse
     */
    public function show(Request $request, ?string $symbol = null): \Illuminate\View\View|RedirectResponse
    {
        // ----------------------------------------------------------------------
        // ส่วนที่เพิ่มใหม่: ดักจับการค้นหาจากฟอร์มหน้าเว็บ
        // ถ้ามี ?symbol= ส่งมา และไม่ตรงกับ Path ปัจจุบัน ให้ Redirect ไปที่ Path ที่ถูกต้อง
        // ----------------------------------------------------------------------
        if ($request->has('symbol') && $request->input('symbol') !== $symbol) {
            $inputSymbol = $this->normalizeSymbol($request->input('symbol'));
            $strategy = $request->query('strategy', 'sma_cross');
            
            return redirect('/stock/' . $inputSymbol . '?strategy=' . $strategy);
        }
        // ----------------------------------------------------------------------

        $symbolOut = $this->normalizeSymbol($symbol ?? 'NVDA');

        $service = app(StockService::class);
        $indicatorService = app(TechnicalIndicatorService::class);
        $aiService = app(OllamaAnalysisService::class);

        $ruleBasedService = app(RuleBasedAnalysisService::class);
        $backtestService = app(BacktestService::class);
        
        $strategy = strtolower(trim((string) $request->query('strategy', 'sma_cross')));
        if (! in_array($strategy, ['rsi', 'sma_cross', 'macd'], true)) {
            $strategy = 'sma_cross';
        }

        $quote = $service->getQuote($symbolOut);
        $timeSeries = $service->getTimeSeries($symbolOut, 200);

        $indicators = [
            'rsi14' => null,
            'sma20' => null,
            'sma50' => null,
            'macd' => [
                'macd' => null,
                'signal' => null,
                'histogram' => null,
            ],
            'signals' => [
                'rsi' => ['label' => 'Neutral', 'color' => 'secondary'],
                'sma50' => ['label' => '-', 'color' => 'secondary'],
                'macd' => ['label' => 'Neutral', 'color' => 'secondary'],
            ],
        ];

        $backtestResult = [
            'ok' => false,
            'strategy' => $strategy,
            'strategy_return_pct' => null,
            'buy_hold_return_pct' => null,
            'num_trades' => 0,
            'win_rate' => null,
            'final_value' => null,
            'trades' => [],
            'error' => 'Unable to run backtest because historical series was not available.',
        ];

        $ruleBasedAnalysis = [
            'ok' => false,
            'summary' => '',
            'points' => [],
            'error' => 'ไม่มีข้อมูลเพียงพอสำหรับวิเคราะห์',
        ];

        if (($timeSeries['ok'] ?? false) === true) {
            $closes = array_map(static fn (array $row): float => (float) ($row['close'] ?? 0.0), $timeSeries['values']);

            $indicators['rsi14'] = $indicatorService->rsi($closes, 14);
            $indicators['sma20'] = $indicatorService->sma($closes, 20);
            $indicators['sma50'] = $indicatorService->sma($closes, 50);
            $indicators['macd'] = $indicatorService->macd($closes);

            $rsiValue = $indicators['rsi14'];
            if ($rsiValue !== null) {
                if ($rsiValue > 70.0) {
                    $indicators['signals']['rsi'] = ['label' => 'Overbought', 'color' => 'danger'];
                } elseif ($rsiValue < 30.0) {
                    $indicators['signals']['rsi'] = ['label' => 'Oversold', 'color' => 'success'];
                }
            }

            $latestClose = $closes[count($closes) - 1] ?? null;
            if ($latestClose !== null && $indicators['sma50'] !== null) {
                if ($latestClose > $indicators['sma50']) {
                    $indicators['signals']['sma50'] = ['label' => 'ขาขึ้น (Above SMA50)', 'color' => 'success'];
                } elseif ($latestClose < $indicators['sma50']) {
                    $indicators['signals']['sma50'] = ['label' => 'ขาลง (Below SMA50)', 'color' => 'danger'];
                } else {
                    $indicators['signals']['sma50'] = ['label' => 'Sideways', 'color' => 'secondary'];
                }
            }

            $histogram = $indicators['macd']['histogram'];
            if ($histogram !== null) {
                if ($histogram > 0.0) {
                    $indicators['signals']['macd'] = ['label' => 'Bullish', 'color' => 'success'];
                } elseif ($histogram < 0.0) {
                    $indicators['signals']['macd'] = ['label' => 'Bearish', 'color' => 'danger'];
                }
            }

            $backtestResult = $backtestService->run($timeSeries['values'], $strategy);
        }

        $stockData = [
            'symbol' => $symbolOut,
            'name' => $quote['name'] ?? null,
            'close' => $quote['close'] ?? null,
            'percent_change' => $quote['percent_change'] ?? null,
            'rsi' => $indicators['rsi14'],
            'sma20' => $indicators['sma20'],
            'sma50' => $indicators['sma50'],
            'macd_histogram' => $indicators['macd']['histogram'] ?? null,
            'rsi_signal' => $indicators['signals']['rsi']['label'] ?? 'Neutral',
            'trend_signal' => $indicators['signals']['sma50']['label'] ?? '-',
            'macd_signal' => $indicators['signals']['macd']['label'] ?? 'Neutral',
        ];

        $aiAnalysis = $aiService->analyze($stockData);
        $ruleBasedAnalysis = $ruleBasedService->analyze($stockData);

        return View::make('stock.show_phase5', [
            'symbol' => $symbolOut,
            'quote' => $quote,
            'timeSeries' => $timeSeries,
            'indicators' => $indicators,
            'aiAnalysis' => $aiAnalysis,
            'ruleBasedAnalysis' => $ruleBasedAnalysis,
            'errorMessage' => $this->buildFriendlyError($quote, $timeSeries),
            'strategy' => $strategy,
            'backtestResult' => $backtestResult,
        ]);
    }

    public function chartData(string $symbol): JsonResponse
    {
        $symbolOut = $this->normalizeSymbol($symbol);

        $service = app(StockService::class);
        $indicatorService = app(TechnicalIndicatorService::class);
        $timeSeries = $service->getTimeSeries($symbolOut, 30);

        if (($timeSeries['ok'] ?? false) !== true) {
            return response()->json([
                'ok' => false,
                'symbol' => $symbolOut,
                'error' => (string) ($timeSeries['error'] ?? 'Unable to fetch chart data.'),
                'labels' => [],
                'series' => [],
                'sma20' => [],
                'sma50' => [],
            ], 200);
        }

        $labels = [];
        $series = [];
        $closes = [];

        foreach (($timeSeries['values'] ?? []) as $row) {
            $labels[] = (string) ($row['datetime'] ?? '');
            $close = (float) ($row['close'] ?? 0);
            $series[] = $close;
            $closes[] = $close;
        }

        return response()->json([
            'ok' => true,
            'symbol' => $symbolOut,
            'labels' => $labels,
            'series' => $series,
            'sma20' => $indicatorService->smaSeries($closes, 20),
            'sma50' => $indicatorService->smaSeries($closes, 50),
        ]);
    }

    private function normalizeSymbol(?string $symbol): string
    {
        $value = strtoupper((string) ($symbol ?? 'NVDA'));
        $value = preg_replace('/[^A-Z0-9._-]/', '', $value);

        return $value !== '' ? $value : 'NVDA';
    }

    /**
     * @param array{ok: bool, error: ?string} $quote
     * @param array{ok: bool, error: ?string} $timeSeries
     */
    private function buildFriendlyError(array $quote, array $timeSeries): ?string
    {
        $quoteOk = ($quote['ok'] ?? false) === true;
        $timeOk = ($timeSeries['ok'] ?? false) === true;

        if ($quoteOk && $timeOk) {
            return null;
        }

        $quoteErr = (string) ($quote['error'] ?? '');
        $seriesErr = (string) ($timeSeries['error'] ?? '');

        $messages = [];
        if (! $quoteOk && $quoteErr !== '') {
            $messages[] = 'Quote: ' . $quoteErr;
        }
        if (! $timeOk && $seriesErr !== '') {
            $messages[] = 'Chart: ' . $seriesErr;
        }

        $combined = implode(' ', $messages);

        if ($combined === '') {
            $combined = 'Unable to fetch stock data right now.';
        }

        return $combined;
    }
}