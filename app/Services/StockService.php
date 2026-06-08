<?php

namespace App\Services;

use App\Models\StockPrice;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class StockService
{
    private const TIME_SERIES_TTL_SECONDS = 15 * 60;

    private const QUOTE_TTL_SECONDS = 15 * 60;

    /**
     * Get latest close & percent change from DB.
     *
     * percent_change is computed from close_latest vs close_prev:
     * (close_latest - close_prev) / close_prev * 100
     *
     * @param non-empty-string $symbol
     * @return array{ok: bool, close: ?float, percent_change: ?float}
     */
    public function getLatestFromDb(string $symbol): array
    {
        $symbol = strtoupper(trim($symbol));

        if ($symbol === '') {
            return [
                'ok' => false,
                'close' => null,
                'percent_change' => null,
            ];
        }

        $rows = StockPrice::where('symbol', $symbol)
            ->orderByDesc('trade_date')
            ->limit(2)
            ->get(['trade_date', 'close']);

        if ($rows->isEmpty()) {
            return [
                'ok' => false,
                'close' => null,
                'percent_change' => null,
            ];
        }

        $closeLatest = (float) $rows[0]->close;
        if (! is_finite($closeLatest)) {
            return [
                'ok' => false,
                'close' => null,
                'percent_change' => null,
            ];
        }

        if ($rows->count() < 2) {
            return [
                'ok' => true,
                'close' => $closeLatest,
                'percent_change' => null,
            ];
        }

        $closePrev = (float) $rows[1]->close;
        if (! is_finite($closePrev) || $closePrev == 0.0) {
            return [
                'ok' => true,
                'close' => $closeLatest,
                'percent_change' => null,
            ];
        }

        $percentChange = (($closeLatest - $closePrev) / $closePrev) * 100.0;
        if (! is_finite($percentChange)) {
            return [
                'ok' => true,
                'close' => $closeLatest,
                'percent_change' => null,
            ];
        }

        return [
            'ok' => true,
            'close' => $closeLatest,
            'percent_change' => $percentChange,
        ];
    }

    /**
     * Fetch Twelve Data time_series for chart.

     *
     * API returns newest -> oldest. We reverse to oldest -> newest for Chart.js.
     *
     * @param non-empty-string $symbol
     * @return array{ok: bool, symbol: string, name: ?string, values: array<int, array{datetime: string, close: float}>, error: ?string}
     */
    public function getTimeSeries(string $symbol, int $outputSize = 30): array
    {
        $symbol = strtoupper(trim($symbol));
 
        if ($symbol === '') {
            return [
                'ok' => false,
                'symbol' => 'NVDA',
                'name' => null,
                'values' => [],
                'error' => 'Invalid symbol.',
            ];
        }
 
        return Cache::remember(
            $this->timeSeriesCacheKey($symbol, $outputSize),
            self::TIME_SERIES_TTL_SECONDS,
            function () use ($symbol, $outputSize): array {
                $dbValues = $this->getDbTimeSeries($symbol, $outputSize);

                if ($dbValues !== [] && $this->hasFreshDbData($symbol)) {
                    return [
                        'ok' => true,
                        'symbol' => $symbol,
                        'name' => null,
                        'values' => $dbValues,
                        'error' => null,
                    ];
                }

                $apiResult = $this->fetchAndStoreTimeSeries($symbol, $outputSize);
                if ($apiResult['ok']) {
                    return $apiResult;
                }

                if ($dbValues !== []) {
                    return [
                        'ok' => true,
                        'symbol' => $symbol,
                        'name' => null,
                        'values' => $dbValues,
                        'error' => null,
                    ];
                }

                return $apiResult;
            }
        );
    }
 
    /**
     * Fetch Twelve Data quote for summary.
     *
     * @param non-empty-string $symbol
     * @return array{ok: bool, symbol: string, name: ?string, close: ?float, change: ?float, percent_change: ?float, error: ?string}
     */
    public function getQuote(string $symbol): array
    {
        $symbol = strtoupper(trim($symbol));
 
        if ($symbol === '') {
            return [
                'ok' => false,
                'symbol' => 'NVDA',
                'name' => null,
                'close' => null,
                'change' => null,
                'percent_change' => null,
                'error' => 'Invalid symbol.',
            ];
        }
 
        return Cache::remember(
            $this->quoteCacheKey($symbol),
            self::QUOTE_TTL_SECONDS,
            function () use ($symbol): array {
                try {
                    $apiKey = (string) env('TWELVEDATA_API_KEY', '');
                    if ($apiKey === '') {
                        return [
                            'ok' => false,
                            'symbol' => $symbol,
                            'name' => null,
                            'close' => null,
                            'change' => null,
                            'percent_change' => null,
                            'error' => 'Missing TWELVEDATA_API_KEY in .env',
                        ];
                    }
 
                    $url = 'https://api.twelvedata.com/quote';
 
                    // withoutVerifying(): local XAMPP dev workaround for SSL cert (cURL error 60).
                    // Remove on production and configure a proper CA bundle instead.
                    $response = Http::withoutVerifying()->timeout(15)->get($url, [
                        'symbol' => $symbol,
                        'apikey' => $apiKey,
                    ]);
 
                    if (! $response->successful()) {
                        return [
                            'ok' => false,
                            'symbol' => $symbol,
                            'name' => null,
                            'close' => null,
                            'change' => null,
                            'percent_change' => null,
                            'error' => 'Failed to fetch quote (HTTP error).',
                        ];
                    }
 
                    $payload = $response->json();
                    if (! is_array($payload)) {
                        return [
                            'ok' => false,
                            'symbol' => $symbol,
                            'name' => null,
                            'close' => null,
                            'change' => null,
                            'percent_change' => null,
                            'error' => 'Invalid API response for quote.',
                        ];
                    }
 
                    $status = (string) Arr::get($payload, 'status', '');
                    if ($status !== '' && $status !== 'ok') {
                        $apiError = (string) Arr::get($payload, 'message', Arr::get($payload, 'error', ''));
                        return [
                            'ok' => false,
                            'symbol' => $symbol,
                            'name' => null,
                            'close' => null,
                            'change' => null,
                            'percent_change' => null,
                            'error' => $apiError !== '' ? $apiError : 'Quote API returned an error.',
                        ];
                    }
 
                    $closeRaw = Arr::get($payload, 'close');
                    $changeRaw = Arr::get($payload, 'change');
                    $percentChangeRaw = Arr::get($payload, 'percent_change');
 
                    $name = Arr::get($payload, 'name');
                    $nameOut = $name === null ? null : (string) $name;
 
                    if ($closeRaw === null) {
                        return [
                            'ok' => false,
                            'symbol' => $symbol,
                            'name' => $nameOut,
                            'close' => null,
                            'change' => null,
                            'percent_change' => null,
                            'error' => 'Quote data missing for this symbol.',
                        ];
                    }
 
                    $close = (float) $closeRaw;
                    $change = $changeRaw === null ? null : (float) $changeRaw;
                    $percentChange = $percentChangeRaw === null ? null : (float) $percentChangeRaw;
 
                    return [
                        'ok' => is_finite($close),
                        'symbol' => $symbol,
                        'name' => $nameOut,
                        'close' => is_finite($close) ? $close : null,
                        'change' => $change,
                        'percent_change' => $percentChange,
                        'error' => is_finite($close) ? null : 'Quote close value is invalid.',
                    ];
                } catch (\Throwable $e) {
                    return [
                        'ok' => false,
                        'symbol' => $symbol,
                        'name' => null,
                        'close' => null,
                        'change' => null,
                        'percent_change' => null,
                        'error' => 'Unexpected error while fetching quote.',
                    ];
                }
            }
        );
    }
 
    /**
     * Determine whether the DB has fresh enough time series data.
     */
    private function hasFreshDbData(string $symbol): bool
    {
        $latest = StockPrice::where('symbol', $symbol)
            ->orderByDesc('trade_date')
            ->first(['trade_date']);

        if ($latest === null) {
            return false;
        }

        $latestDate = Carbon::parse($latest->trade_date);
        return $latestDate->greaterThanOrEqualTo(Carbon::now()->subDay()->startOfDay());
    }

    /**
     * Get time series values from the local DB.
     *
     * @return array<int, array{datetime: string, close: float}>
     */
    private function getDbTimeSeries(string $symbol, int $outputSize): array
    {
        $prices = StockPrice::where('symbol', $symbol)
            ->orderByDesc('trade_date')
            ->limit($outputSize)
            ->get(['trade_date', 'close'])
            ->reverse()
            ->values();

        $values = [];
        foreach ($prices as $price) {
            $values[] = [
                'datetime' => $price->trade_date->toDateString(),
                'close' => (float) $price->close,
            ];
        }

        return $values;
    }

    /**
     * Fetch time series from API and upsert into DB.
     *
     * @return array{ok: bool, symbol: string, name: ?string, values: array<int, array{datetime: string, close: float}>, error: ?string}
     */
    private function fetchAndStoreTimeSeries(string $symbol, int $outputSize): array
    {
        try {
            $apiKey = (string) env('TWELVEDATA_API_KEY', '');
            if ($apiKey === '') {
                return [
                    'ok' => false,
                    'symbol' => $symbol,
                    'name' => null,
                    'values' => [],
                    'error' => 'Missing TWELVEDATA_API_KEY in .env',
                ];
            }

            $url = 'https://api.twelvedata.com/time_series';
            $response = Http::withoutVerifying()->timeout(15)->get($url, [
                'symbol' => $symbol,
                'interval' => '1day',
                'outputsize' => $outputSize,
                'apikey' => $apiKey,
            ]);

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'symbol' => $symbol,
                    'name' => null,
                    'values' => [],
                    'error' => 'Failed to fetch time series (HTTP error).',
                ];
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                return [
                    'ok' => false,
                    'symbol' => $symbol,
                    'name' => null,
                    'values' => [],
                    'error' => 'Invalid API response for time series.',
                ];
            }

            $status = (string) Arr::get($payload, 'status', '');
            if ($status !== 'ok') {
                $apiError = (string) Arr::get($payload, 'message', Arr::get($payload, 'error', ''));
                return [
                    'ok' => false,
                    'symbol' => $symbol,
                    'name' => null,
                    'values' => [],
                    'error' => $apiError !== '' ? $apiError : 'Time series API returned an error.',
                ];
            }

            $series = (array) Arr::get($payload, 'values', []);
            if ($series === []) {
                return [
                    'ok' => false,
                    'symbol' => $symbol,
                    'name' => null,
                    'values' => [],
                    'error' => 'No time series data found for this symbol.',
                ];
            }

            $rows = [];
            $values = [];
            foreach ($series as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $tradeDate = (string) Arr::get($row, 'datetime', '');
                $closeRaw = Arr::get($row, 'close');

                if ($tradeDate === '' || $closeRaw === null) {
                    continue;
                }

                $close = (float) $closeRaw;
                if (! is_finite($close)) {
                    continue;
                }

                $rows[] = [
                    'symbol' => $symbol,
                    'trade_date' => $tradeDate,
                    'close' => $close,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                $values[] = [
                    'datetime' => $tradeDate,
                    'close' => $close,
                ];
            }

            if ($values === []) {
                return [
                    'ok' => false,
                    'symbol' => $symbol,
                    'name' => null,
                    'values' => [],
                    'error' => 'Time series data could not be parsed.',
                ];
            }

            if ($rows !== []) {
                StockPrice::upsert($rows, ['symbol', 'trade_date'], ['close', 'updated_at']);
            }

            return [
                'ok' => true,
                'symbol' => $symbol,
                'name' => null,
                'values' => array_reverse($values),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'symbol' => $symbol,
                'name' => null,
                'values' => [],
                'error' => 'Unexpected error while fetching time series.',
            ];
        }
    }

    /**
     * @param non-empty-string $symbol
     */
    private function timeSeriesCacheKey(string $symbol, int $outputSize): string
    {
        return sprintf('twelvedata:time_series:%s:%d', $symbol, $outputSize);
    }

    /**
     * @param non-empty-string $symbol
     */
    private function quoteCacheKey(string $symbol): string
    {
        return sprintf('twelvedata:quote:%s', $symbol);
    }
}