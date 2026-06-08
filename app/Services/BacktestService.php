<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;

final class BacktestService
{
    private const VALID_STRATEGIES = ['rsi', 'sma_cross', 'macd'];

    public function __construct(private TechnicalIndicatorService $indicatorService)
    {
    }

    /**
     * @param array<int, array{datetime: string, close: float}> $candles
     * @return array{
     *     ok: bool,
     *     strategy: string,
     *     strategy_return_pct: ?float,
     *     buy_hold_return_pct: ?float,
     *     num_trades: int,
     *     win_rate: ?float,
     *     final_value: ?float,
     *     trades: array<int, array{entry_date: string, entry_price: float, exit_date: string, exit_price: float, profit_pct: float}>,
     *     error: ?string
     * }
     */
    public function run(array $candles, string $strategy, float $initialCapital = 10000.0): array
    {
        $strategy = $this->normalizeStrategy($strategy);
        if (! in_array($strategy, self::VALID_STRATEGIES, true)) {
            return [
                'ok' => false,
                'strategy' => $strategy,
                'strategy_return_pct' => null,
                'buy_hold_return_pct' => null,
                'num_trades' => 0,
                'win_rate' => null,
                'final_value' => null,
                'trades' => [],
                'error' => 'กลยุทธ์ที่เลือกไม่ถูกต้อง',
            ];
        }

        $closes = [];
        $dates = [];
        foreach ($candles as $row) {
            if (! is_array($row)) {
                return $this->invalidCandlesResponse($strategy);
            }

            $datetime = (string) Arr::get($row, 'datetime', '');
            $closeRaw = Arr::get($row, 'close');

            if ($datetime === '' || $closeRaw === null || ! is_numeric($closeRaw)) {
                return $this->invalidCandlesResponse($strategy);
            }

            $closes[] = (float) $closeRaw;
            $dates[] = $datetime;
        }

        $count = count($closes);
        if ($count < 2) {
            return [
                'ok' => false,
                'strategy' => $strategy,
                'strategy_return_pct' => null,
                'buy_hold_return_pct' => null,
                'num_trades' => 0,
                'win_rate' => null,
                'final_value' => null,
                'trades' => [],
                'error' => 'ข้อมูลราคาไม่เพียงพอสำหรับ backtest',
            ];
        }

        $cash = $initialCapital;
        $shares = 0.0;
        $holding = false;
        $entryPrice = 0.0;
        $entryDate = '';
        $trades = [];

        for ($index = 1; $index < $count; $index++) {
            $signal = $this->calculateSignal($closes, $strategy, $index);
            $price = $closes[$index];
            $date = $dates[$index];

            if (! $holding && $signal === 'buy') {
                if ($price <= 0.0) {
                    continue;
                }

                $shares = $cash / $price;
                $cash = 0.0;
                $holding = true;
                $entryPrice = $price;
                $entryDate = $date;
                continue;
            }

            if ($holding && $signal === 'sell') {
                $cash = $shares * $price;
                $profitPct = $entryPrice > 0.0 ? (($price - $entryPrice) / $entryPrice) * 100.0 : 0.0;
                $trades[] = [
                    'entry_date' => $entryDate,
                    'entry_price' => $entryPrice,
                    'exit_date' => $date,
                    'exit_price' => $price,
                    'profit_pct' => round($profitPct, 2),
                ];
                $shares = 0.0;
                $holding = false;
            }
        }

        $lastClose = $closes[$count - 1];
        if ($holding) {
            $cash = $shares * $lastClose;
            $shares = 0.0;
        }

        $finalValue = $cash;
        $strategyReturnPct = $initialCapital > 0.0 ? (($finalValue - $initialCapital) / $initialCapital) * 100.0 : null;
        $firstClose = $closes[0];
        $buyHoldReturnPct = $firstClose > 0.0 ? (($lastClose - $firstClose) / $firstClose) * 100.0 : null;
        $numTrades = count($trades);
        $winCount = 0;
        foreach ($trades as $trade) {
            if (($trade['exit_price'] - $trade['entry_price']) > 0.0) {
                $winCount++;
            }
        }
        $winRate = $numTrades > 0 ? ($winCount / $numTrades) * 100.0 : 0.0;

        return [
            'ok' => true,
            'strategy' => $strategy,
            'strategy_return_pct' => round($strategyReturnPct, 2),
            'buy_hold_return_pct' => $buyHoldReturnPct === null ? null : round($buyHoldReturnPct, 2),
            'num_trades' => $numTrades,
            'win_rate' => round($winRate, 2),
            'final_value' => round($finalValue, 2),
            'trades' => $trades,
            'error' => null,
        ];
    }

    /**
     * @param list<float> $closes
     */
    private function calculateSignal(array $closes, string $strategy, int $index): string
    {
        if ($strategy === 'rsi') {
            return $this->calculateRsiSignal($closes, $index);
        }

        if ($strategy === 'sma_cross') {
            return $this->calculateSmaCrossSignal($closes, $index);
        }

        if ($strategy === 'macd') {
            return $this->calculateMacdSignal($closes, $index);
        }

        return 'none';
    }

    /**
     * @param list<float> $closes
     */
    private function calculateRsiSignal(array $closes, int $index): string
    {
        $slice = array_slice($closes, 0, $index + 1);
        $rsi = $this->indicatorService->rsi($slice, 14);

        if ($rsi === null) {
            return 'none';
        }

        if ($rsi < 30.0) {
            return 'buy';
        }

        if ($rsi > 70.0) {
            return 'sell';
        }

        return 'none';
    }

    /**
     * @param list<float> $closes
     */
    private function calculateSmaCrossSignal(array $closes, int $index): string
    {
        if ($index < 1) {
            return 'none';
        }

        $currentSlice = array_slice($closes, 0, $index + 1);
        $previousSlice = array_slice($closes, 0, $index);

        $currentSma20 = $this->indicatorService->sma($currentSlice, 20);
        $currentSma50 = $this->indicatorService->sma($currentSlice, 50);
        $previousSma20 = $this->indicatorService->sma($previousSlice, 20);
        $previousSma50 = $this->indicatorService->sma($previousSlice, 50);

        if ($currentSma20 === null || $currentSma50 === null || $previousSma20 === null || $previousSma50 === null) {
            return 'none';
        }

        if ($currentSma20 > $currentSma50 && $previousSma20 <= $previousSma50) {
            return 'buy';
        }

        if ($currentSma20 < $currentSma50 && $previousSma20 >= $previousSma50) {
            return 'sell';
        }

        return 'none';
    }

    /**
     * @param list<float> $closes
     */
    private function calculateMacdSignal(array $closes, int $index): string
    {
        if ($index < 1) {
            return 'none';
        }

        $currentSlice = array_slice($closes, 0, $index + 1);
        $previousSlice = array_slice($closes, 0, $index);

        $currentMacd = $this->indicatorService->macd($currentSlice);
        $previousMacd = $this->indicatorService->macd($previousSlice);

        if ($currentMacd['macd'] === null || $currentMacd['signal'] === null || $previousMacd['macd'] === null || $previousMacd['signal'] === null) {
            return 'none';
        }

        if ($currentMacd['macd'] > $currentMacd['signal'] && $previousMacd['macd'] <= $previousMacd['signal']) {
            return 'buy';
        }

        if ($currentMacd['macd'] < $currentMacd['signal'] && $previousMacd['macd'] >= $previousMacd['signal']) {
            return 'sell';
        }

        return 'none';
    }

    private function normalizeStrategy(string $strategy): string
    {
        return strtolower(trim($strategy));
    }

    private function invalidCandlesResponse(string $strategy): array
    {
        return [
            'ok' => false,
            'strategy' => $strategy,
            'strategy_return_pct' => null,
            'buy_hold_return_pct' => null,
            'num_trades' => 0,
            'win_rate' => null,
            'final_value' => null,
            'trades' => [],
            'error' => 'ข้อมูลราคาไม่ถูกต้องสำหรับ backtest',
        ];
    }
}
