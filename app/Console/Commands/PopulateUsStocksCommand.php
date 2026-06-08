<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\UsStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PopulateUsStocksCommand extends Command
{
    protected $signature = 'us:stocks:populate {--limit=500 : Max symbols to import (0 = no limit)} {--refresh : Re-fetch and upsert (default does upsert)}';

    protected $description = 'Populate US stocks universe/listings into DB using Twelve Data endpoint /stocks?country=United States';

    public function handle(): int
    {
        $apiKey = (string) env('TWELVEDATA_API_KEY', '');
        if ($apiKey === '') {
            $this->error('Missing TWELVEDATA_API_KEY in .env');
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $refresh = (bool) $this->option('refresh');

        if ($refresh) {
            UsStock::query()->truncate();
        }

        $url = 'https://api.twelvedata.com/stocks';

        // NOTE: API may return paginated results depending on Twelve Data config.
        // Here we implement a simple single-call import; if you see incomplete results,
        // we can extend to pagination once we confirm response fields.
        $response = Http::withoutVerifying()->timeout(30)->get($url, [
            'country' => 'United States',
            'apikey' => $apiKey,
        ]);

        if (! $response->successful()) {
            $this->error('Failed to fetch /stocks from Twelve Data. HTTP=' . $response->status());
            Log::error('PopulateUsStocksCommand: fetch failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return self::FAILURE;
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            $this->error('Invalid JSON payload from Twelve Data /stocks');
            return self::FAILURE;
        }

        // Expected patterns:
        // - either {data: [...]}
        // - or an array directly
        // - symbol fields may be 'symbol' or 'ticker'
        $rows = $payload['data'] ?? $payload;
        if (! is_array($rows)) {
            $this->error('Unexpected payload structure from /stocks');
            return self::FAILURE;
        }

        $imported = 0;
        $upserts = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $symbol = strtoupper((string) ($row['symbol'] ?? $row['ticker'] ?? ''));
            if ($symbol === '') {
                continue;
            }

            $upserts[] = [
                'symbol' => $symbol,
                'name' => $row['name'] ?? null,
                'exchange' => $row['exchange'] ?? ($row['mic'] ?? null),
                'type' => $row['type'] ?? ($row['security_type'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $imported++;
            if ($limit > 0 && $imported >= $limit) {
                break;
            }
        }

        if ($upserts === []) {
            $this->warn('No symbols to import.');
            return self::SUCCESS;
        }

        // Upsert by symbol
        UsStock::upsert($upserts, ['symbol'], ['name', 'exchange', 'type', 'updated_at']);

        $this->info('Imported/updated US stocks: ' . count($upserts));
        return self::SUCCESS;
    }
}

