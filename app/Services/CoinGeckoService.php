<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class CoinGeckoService
{
    /**
     * Hardcoded mapping for common crypto symbols.
     * Note: COINGECKO ids are lowercase.
     *
     * @var array<string, string>
     */
    private const COIN_MAP = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'BNB' => 'binancecoin',
        'SOL' => 'solana',
        'XRP' => 'ripple',
        'ADA' => 'cardano',
        'DOGE' => 'dogecoin',
        'AVAX' => 'avalanche-2',
        'DOT' => 'polkadot',
        'LINK' => 'chainlink',
        'LTC' => 'litecoin',
        'MATIC' => 'matic-network',
        'UNI' => 'uniswap',
        'ATOM' => 'cosmos',
        'XLM' => 'stellar',
        'ETC' => 'ethereum-classic',
        'BCH' => 'bitcoin-cash',
        'ALGO' => 'algorand',
        'VET' => 'vechain',
        'TRX' => 'tron',
        'ICP' => 'internet-computer',
        'FIL' => 'filecoin',
        'HBAR' => 'hedera-hashgraph',
        'APT' => 'aptos',
        'ARB' => 'arbitrum',
        'OP' => 'optimism',
        'INJ' => 'injective-protocol',
        'SUI' => 'sui',
        'TON' => 'the-open-network',
        'NEAR' => 'near',
        'FTM' => 'fantom',
        'SAND' => 'the-sandbox',
        'MANA' => 'decentraland',
        'AAVE' => 'aave',
        'SUSHI' => 'sushi',
        'COMP' => 'compound-governance-token',
        'MKR' => 'maker',
        'SNX' => 'havven',
        'CRV' => 'curve-dao-token',
        'ENJ' => 'enjincoin',
        'ZIL' => 'zilliqa',
        'BAT' => 'basic-attention-token',
        'DASH' => 'dash',
        'ZEC' => 'zcash',
        'XMR' => 'monero',
        'XTZ' => 'tezos',
        'EOS' => 'eos',
        'THETA' => 'theta-token',
        'KCS' => 'kucoin-shares',
        'SHIB' => 'shiba-inu',
    ];

    private const QUOTE_TTL_SECONDS = 10 * 60;

    private const CHART_TTL_SECONDS = 15 * 60;

    public function isCrypto(string $symbol): bool
    {
        $symbol = strtoupper(trim($symbol));

        if ($symbol === '') {
            return false;
        }

        return array_key_exists($symbol, self::COIN_MAP);
    }

    public function getCoinId(string $symbol): ?string
    {
        $symbol = strtoupper(trim($symbol));

        if ($symbol === '') {
            return null;
        }

        return self::COIN_MAP[$symbol] ?? null;
    }

    /**
     * @return array{ok: bool, name: ?string, close: ?float, percent_change: ?float, error: ?string}
     */
    public function getQuote(string $symbol): array
    {
        $symbol = strtoupper(trim($symbol));

        if ($symbol === '') {
            return [
                'ok' => false,
                'name' => null,
                'close' => null,
                'percent_change' => null,
                'error' => 'Invalid symbol.',
            ];
        }

        $coinId = $this->getCoinId($symbol);
        if ($coinId === null) {
            return [
                'ok' => false,
                'name' => null,
                'close' => null,
                'percent_change' => null,
                'error' => 'Unknown crypto symbol.',
            ];
        }

        return Cache::remember(
            $this->quoteCacheKey($coinId),
            self::QUOTE_TTL_SECONDS,
            function () use ($coinId, $symbol): array {
                try {
                    $url = 'https://api.coingecko.com/api/v3/simple/price';

                    $res = Http::withoutVerifying()
                        ->timeout(10)
                        ->get($url, [
                            'ids' => $coinId,
                            'vs_currencies' => 'usd',
                            'include_24hr_change' => 'true',
                            'include_last_updated_at' => 'true',
                        ]);

                    if (! $res->successful()) {
                        return [
                            'ok' => false,
                            'name' => null,
                            'close' => null,
                            'percent_change' => null,
                            'error' => 'Failed to fetch quote (HTTP error).',
                        ];
                    }

                    $payload = $res->json();
                    if (! is_array($payload)) {
                        return [
                            'ok' => false,
                            'name' => null,
                            'close' => null,
                            'percent_change' => null,
                            'error' => 'Invalid API response for quote.',
                        ];
                    }

                    $item = Arr::get($payload, $coinId);
                    if (! is_array($item)) {
                        return [
                            'ok' => false,
                            'name' => null,
                            'close' => null,
                            'percent_change' => null,
                            'error' => 'Quote data missing for this coin.',
                        ];
                    }

                    $closeRaw = Arr::get($item, 'usd');
                    $percentChangeRaw = Arr::get($item, 'usd_24h_change');

                    if ($closeRaw === null) {
                        return [
                            'ok' => false,
                            'name' => null,
                            'close' => null,
                            'percent_change' => null,
                            'error' => 'Quote close value missing.',
                        ];
                    }

                    $close = (float) $closeRaw;
                    if (! is_finite($close)) {
                        return [
                            'ok' => false,
                            'name' => null,
                            'close' => null,
                            'percent_change' => null,
                            'error' => 'Quote close value is invalid.',
                        ];
                    }

                    $percentChange = $percentChangeRaw === null ? null : (float) $percentChangeRaw;

                    $name = $this->humanNameFromSymbol($symbol);

                    return [
                        'ok' => true,
                        'name' => $name,
                        'close' => $close,
                        'percent_change' => is_finite($percentChange) ? $percentChange : null,
                        'error' => null,
                    ];
                } catch (\Throwable $e) {
                    return [
                        'ok' => false,
                        'name' => null,
                        'close' => null,
                        'percent_change' => null,
                        'error' => 'Unexpected error while fetching quote.',
                    ];
                }
            }
        );
    }

    /**
     * @return array{ok: bool, values: array<int, array{datetime: string, close: float}>, error: ?string}
     */
    public function getMarketChart(string $symbol, int $outputSize = 200): array
    {
        $symbol = strtoupper(trim($symbol));

        if ($symbol === '' || $outputSize <= 0) {
            return [
                'ok' => false,
                'values' => [],
                'error' => 'Invalid arguments.',
            ];
        }

        $coinId = $this->getCoinId($symbol);
        if ($coinId === null) {
            return [
                'ok' => false,
                'values' => [],
                'error' => 'Unknown crypto symbol.',
            ];
        }

        return Cache::remember(
            $this->chartCacheKey($coinId, $outputSize),
            self::CHART_TTL_SECONDS,
            function () use ($coinId, $outputSize): array {
                try {
                    $url = sprintf('https://api.coingecko.com/api/v3/coins/%s/market_chart', $coinId);

                    $res = Http::withoutVerifying()
                        ->timeout(10)
                        ->get($url, [
                            'vs_currency' => 'usd',
                            'days' => 90,
                            'interval' => 'daily',
                        ]);

                    if (! $res->successful()) {
                        return [
                            'ok' => false,
                            'values' => [],
                            'error' => 'Failed to fetch market chart (HTTP error).',
                        ];
                    }

                    $payload = $res->json();
                    if (! is_array($payload)) {
                        return [
                            'ok' => false,
                            'values' => [],
                            'error' => 'Invalid API response for market chart.',
                        ];
                    }

                    $prices = Arr::get($payload, 'prices');
                    if (! is_array($prices) || $prices === []) {
                        return [
                            'ok' => false,
                            'values' => [],
                            'error' => 'Market chart prices missing.',
                        ];
                    }

                    $values = [];
                    foreach ($prices as $pair) {
                        if (! is_array($pair) || count($pair) < 2) {
                            continue;
                        }

                        $tsMs = $pair[0];
                        $closeRaw = $pair[1];

                        if ($tsMs === null || $closeRaw === null) {
                            continue;
                        }

                        $tsMsNum = (int) $tsMs;
                        if ($tsMsNum <= 0) {
                            continue;
                        }

                        $close = (float) $closeRaw;
                        if (! is_finite($close)) {
                            continue;
                        }

                        $dt = date('Y-m-d', (int) floor($tsMsNum / 1000));

                        $values[] = [
                            'datetime' => $dt,
                            'close' => $close,
                        ];
                    }

                    if ($values === []) {
                        return [
                            'ok' => false,
                            'values' => [],
                            'error' => 'Market chart data could not be parsed.',
                        ];
                    }

                    // CoinGecko market_chart returns oldest -> newest already for prices.
                    // Keep as-is, but ensure slicing to latest.
                    if (count($values) > $outputSize) {
                        $values = array_slice($values, -$outputSize);
                    }


                    return [
                        'ok' => true,
                        'values' => $values,
                        'error' => null,
                    ];
                } catch (\Throwable $e) {
                    return [
                        'ok' => false,
                        'values' => [],
                        'error' => 'Unexpected error while fetching market chart.',
                    ];
                }
            }
        );
    }

    private function quoteCacheKey(string $coinId): string
    {
        return sprintf('coingecko:quote:%s', $coinId);
    }

    private function chartCacheKey(string $coinId, int $outputSize): string
    {
        return sprintf('coingecko:chart:%s:%d', $coinId, $outputSize);
    }

    private function humanNameFromSymbol(string $symbol): string
    {
        $symbol = strtoupper(trim($symbol));

        if (! isset(self::COIN_MAP[$symbol])) {
            return $symbol;
        }

        // Minimal human-readable mapping (no extra API calls). Example: Bitcoin (BTC)
        $coinId = self::COIN_MAP[$symbol];
        $title = str_replace('-', ' ', $coinId);
        $title = ucwords($title);

        return sprintf('%s (%s)', $title, $symbol);
    }
}

