<?php

declare(strict_types=1);

namespace App\Services;

final class TechnicalIndicatorService
{
    /**
     * @param list<float> $closes
     */
    public function sma(array $closes, int $period): ?float
    {
        $count = count($closes);
        if ($count < $period) {
            return null;
        }

        $sum = array_sum(array_slice($closes, $count - $period, $period));

        return $sum / $period;
    }

    /**
     * @param list<float> $closes
     */
    public function ema(array $closes, int $period): ?float
    {
        $series = $this->emaSeries($closes, $period);

        if ($series === []) {
            return null;
        }

        $last = $series[count($series) - 1];

        return $last === null ? null : $last;
    }

    /**
     * @param list<float> $closes
     */
    public function rsi(array $closes, int $period = 14): ?float
    {
        $count = count($closes);
        if ($count < $period + 1) {
            return null;
        }

        $gains = [];
        $losses = [];

        for ($index = 1; $index < $count; $index++) {
            $change = $closes[$index] - $closes[$index - 1];
            $gains[] = $change > 0.0 ? $change : 0.0;
            $losses[] = $change < 0.0 ? -$change : 0.0;
        }

        $initialGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $initialLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        $averageGain = $initialGain;
        $averageLoss = $initialLoss;

        for ($index = $period; $index < count($gains); $index++) {
            $averageGain = ($averageGain * ($period - 1) + $gains[$index]) / $period;
            $averageLoss = ($averageLoss * ($period - 1) + $losses[$index]) / $period;
        }

        if ($averageLoss === 0.0) {
            return 100.0;
        }

        $relativeStrength = $averageGain / $averageLoss;

        return 100.0 - (100.0 / (1.0 + $relativeStrength));
    }

    /**
     * @param list<float> $closes
     * @return array{macd: ?float, signal: ?float, histogram: ?float}
     */
    public function macd(array $closes, int $fast = 12, int $slow = 26, int $signal = 9): array
    {
        if (count($closes) < $slow) {
            return [
                'macd' => null,
                'signal' => null,
                'histogram' => null,
            ];
        }

        $fastEma = $this->emaSeriesInternal($closes, $fast);
        $slowEma = $this->emaSeriesInternal($closes, $slow);


        $macdSeries = [];
        foreach ($fastEma as $index => $value) {
            if (! isset($slowEma[$index])) {
                continue;
            }

            $slowValue = $slowEma[$index];
            if ($value === null || $slowValue === null) {
                continue;
            }

            $macdSeries[] = $value - $slowValue;
        }

        $macdValue = $this->lastArrayValue($macdSeries);
        $signalValue = $this->ema($macdSeries, $signal);

        if ($macdValue === null || $signalValue === null) {
            return [
                'macd' => $macdValue,
                'signal' => $signalValue,
                'histogram' => null,
            ];
        }

        return [
            'macd' => $macdValue,
            'signal' => $signalValue,
            'histogram' => $macdValue - $signalValue,
        ];
    }

    /**
     * @param list<float> $closes
     * @return array<int, float|null>
     */
    public function smaSeries(array $closes, int $period): array
    {
        $count = count($closes);
        if ($count === 0) {
            return [];
        }

        $result = array_fill(0, $count, null);
        if ($count < $period) {
            return $result;
        }

        for ($index = $period - 1; $index < $count; $index++) {
            $slice = array_slice($closes, $index - $period + 1, $period);
            $result[$index] = array_sum($slice) / $period;
        }

        return $result;
    }

    /**
     * @param list<float> $closes
     */
    public function emaSeries(array $closes, int $period): array
    {
        $count = count($closes);
        $result = array_fill(0, $count, null);

        if ($count < $period) {
            return $result;
        }

        $seed = array_sum(array_slice($closes, 0, $period)) / $period;
        $result[$period - 1] = $seed;

        $multiplier = 2.0 / ($period + 1);

        for ($index = $period; $index < $count; $index++) {
            $previous = $result[$index - 1];
            if ($previous === null) {
                continue;
            }

            $result[$index] = ($closes[$index] - $previous) * $multiplier + $previous;
        }

        return $result;
    }

    /**
     * @param list<float> $closes
     * @return array<int, float|null>
     */
    public function rsiSeries(array $closes, int $period = 14): array
    {
        $count = count($closes);
        $result = array_fill(0, $count, null);

        if ($count < $period + 1) {
            return $result;
        }

        $gains = [];
        $losses = [];

        for ($index = 1; $index < $count; $index++) {
            $change = $closes[$index] - $closes[$index - 1];
            $gains[] = $change > 0.0 ? $change : 0.0;
            $losses[] = $change < 0.0 ? -$change : 0.0;
        }

        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        $rsIndex = $period;
        $rs = $avgLoss == 0.0 ? null : ($avgGain / $avgLoss);
        $rsi = $avgLoss == 0.0 ? 100.0 : (100.0 - (100.0 / (1.0 + $rs)));
        $result[$rsIndex] = round($rsi, 2);

        for ($i = $period + 1; $i < $count; $i++) {
            $gain = $gains[$i - 1] ?? 0.0;
            $loss = $losses[$i - 1] ?? 0.0;

            $avgGain = ($avgGain * ($period - 1) + $gain) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + $loss) / $period;

            $rs = $avgLoss == 0.0 ? null : ($avgGain / $avgLoss);
            $rsi = $avgLoss == 0.0 ? 100.0 : (100.0 - (100.0 / (1.0 + $rs)));
            $result[$i] = round($rsi, 2);
        }

        return $result;
    }

    /**
     * @param list<float> $closes
     * @return array{macd_line: array<int, float|null>, signal_line: array<int, float|null>, histogram: array<int, float|null>}
     */
    public function macdSeries(array $closes, int $fast = 12, int $slow = 26, int $signal = 9): array
    {
        $count = count($closes);
        $macdLine = array_fill(0, $count, null);
        $signalLine = array_fill(0, $count, null);
        $histogram = array_fill(0, $count, null);

        if ($count < $slow) {
            return [
                'macd_line' => $macdLine,
                'signal_line' => $signalLine,
                'histogram' => $histogram,
            ];
        }

        $fastEma = $this->emaSeriesInternal($closes, $fast);
        $slowEma = $this->emaSeriesInternal($closes, $slow);


        for ($i = 0; $i < $count; $i++) {
            $f = $fastEma[$i];
            $s = $slowEma[$i];
            if ($f === null || $s === null) {
                continue;
            }
            $macdLine[$i] = round($f - $s, 4);
        }

        $macdValuesForEma = [];
        for ($i = 0; $i < $count; $i++) {
            if ($macdLine[$i] !== null) {
                $macdValuesForEma[] = $macdLine[$i];
            }
        }

        $macdEmaCompact = $this->emaSeries($macdValuesForEma, $signal);

        $compactIndexToOriginalIndex = [];
        $compactIdx = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($macdLine[$i] !== null) {
                $compactIndexToOriginalIndex[$compactIdx] = $i;
                $compactIdx++;
            }
        }

        for ($j = 0; $j < count($macdEmaCompact); $j++) {
            $origIndex = $compactIndexToOriginalIndex[$j] ?? null;
            $val = $macdEmaCompact[$j];
            if ($origIndex === null || $val === null) {
                continue;
            }
            $signalLine[$origIndex] = round($val, 4);
        }

        for ($i = 0; $i < $count; $i++) {
            if ($macdLine[$i] === null || $signalLine[$i] === null) {
                continue;
            }
            $histogram[$i] = round($macdLine[$i] - $signalLine[$i], 4);
        }

        return [
            'macd_line' => $macdLine,
            'signal_line' => $signalLine,
            'histogram' => $histogram,
        ];
    }

    /**
     * @param list<float> $closes
     * @return array<int, float|null>
     */
    private function emaSeriesInternal(array $closes, int $period): array
    {
        $count = count($closes);
        $result = array_fill(0, $count, null);

        if ($count < $period) {
            return $result;
        }

        $seed = array_sum(array_slice($closes, 0, $period)) / $period;
        $result[$period - 1] = $seed;
        $multiplier = 2.0 / ($period + 1);

        for ($index = $period; $index < $count; $index++) {
            $previous = $result[$index - 1];
            if ($previous === null) {
                continue;
            }

            $result[$index] = ($closes[$index] - $previous) * $multiplier + $previous;
        }

        return $result;
    }


    /**
     * @param list<float> $values
     */
    private function lastArrayValue(array $values): ?float
    {
        if ($values === []) {
            return null;
        }

        $last = $values[count($values) - 1];

        return $last === null ? null : $last;
    }
}
