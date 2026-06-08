<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class AiAnalysisService
{
    /**
     * @param array<string, mixed> $stockData
     * @return array{ok: bool, analysis: ?string, error: ?string}
     */
    public function analyze(array $stockData): array
    {
        $apiKey = (string) env('ANTHROPIC_API_KEY', '');
        if ($apiKey === '') {
            return [
                'ok' => false,
                'analysis' => null,
                'error' => 'ยังไม่ได้ตั้งค่า AI (ANTHROPIC_API_KEY) ในระบบ',
            ];
        }

        $cacheKey = $this->buildCacheKey($stockData);

        return Cache::remember($cacheKey, 30 * 60, function () use ($apiKey, $stockData): array {
            try {
                $prompt = $this->buildPrompt($stockData);

                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->withHeaders([
                        'x-api-key' => $apiKey,
                        'anthropic-version' => '2023-06-01',
                        'content-type' => 'application/json',
                    ])
                    ->post('https://api.anthropic.com/v1/messages', [
                        'model' => 'claude-haiku-4-5-20251001',
                        'max_tokens' => 700,
                        'system' => '',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                    ]);

                if (! $response->successful()) {
                    return [
                        'ok' => false,
                        'analysis' => null,
                        'error' => 'AI วิเคราะห์ไม่สำเร็จ (Anthropic API error)',
                    ];
                }

                $payload = $response->json();
                if (! is_array($payload)) {
                    return [
                        'ok' => false,
                        'analysis' => null,
                        'error' => 'AI วิเคราะห์ไม่ได้ผลลัพธ์ที่คาดไว้',
                    ];
                }

                $analysis = $this->extractAnalysisText($payload);
                if ($analysis === null || trim($analysis) === '') {
                    return [
                        'ok' => false,
                        'analysis' => null,
                        'error' => 'AI วิเคราะห์ได้ผลลัพธ์ว่าง',
                    ];
                }

                return [
                    'ok' => true,
                    'analysis' => trim($analysis),
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                return [
                    'ok' => false,
                    'analysis' => null,
                    'error' => 'AI วิเคราะห์ล้มเหลว โปรดลองใหม่ภายหลัง',
                ];
            }
        });
    }

    /**
     * @param array<string, mixed> $stockData
     */
    private function buildCacheKey(array $stockData): string
    {
        $hashable = [
            'symbol' => $stockData['symbol'] ?? '',
            'close' => $stockData['close'] ?? null,
            'percent_change' => $stockData['percent_change'] ?? null,
            'rsi' => $stockData['rsi'] ?? null,
            'sma20' => $stockData['sma20'] ?? null,
            'sma50' => $stockData['sma50'] ?? null,
            'macd_histogram' => $stockData['macd_histogram'] ?? null,
            'rsi_signal' => $stockData['rsi_signal'] ?? '',
            'trend_signal' => $stockData['trend_signal'] ?? '',
            'macd_signal' => $stockData['macd_signal'] ?? '',
        ];

        return sprintf('ai:analysis:%s:%s', strtoupper((string) ($stockData['symbol'] ?? '')), hash('sha256', json_encode($hashable, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)));
    }

    /**
     * @param array<string, mixed> $stockData
     */
    private function buildPrompt(array $stockData): string
    {
        $rows = [];
        $rows[] = 'Symbol: ' . ($stockData['symbol'] ?? 'N/A');
        $rows[] = 'Name: ' . ($stockData['name'] ?? 'N/A');
        $rows[] = 'Latest close: ' . ($this->formatNumber($stockData['close']));
        $rows[] = 'Percent change: ' . ($this->formatPercent($stockData['percent_change']));
        $rows[] = 'RSI: ' . ($this->formatNumber($stockData['rsi']));
        $rows[] = 'SMA20: ' . ($this->formatNumber($stockData['sma20']));
        $rows[] = 'SMA50: ' . ($this->formatNumber($stockData['sma50']));
        $rows[] = 'MACD histogram: ' . ($this->formatNumber($stockData['macd_histogram']));
        $rows[] = 'RSI signal: ' . ($stockData['rsi_signal'] ?? 'N/A');
        $rows[] = 'Trend signal: ' . ($stockData['trend_signal'] ?? 'N/A');
        $rows[] = 'MACD signal: ' . ($stockData['macd_signal'] ?? 'N/A');

        $dataText = implode("\n", $rows);

        return "โปรดวิเคราะห์ข้อมูลด้านเทคนิคหุ้นต่อไปนี้เป็นภาษาไทย โดยอธิบายเฉพาะจากตัวเลขและสัญญาณที่ส่งให้เท่านั้น ไม่ต้องเพิ่มเติมข่าวสารหรือความคิดเห็นภายนอก และห้ามให้คำแนะนำซื้อ ขาย หรือถือ:\n\n" .
            $dataText .
            "\n\nกรุณาสรุปเป็นภาษาไทยสั้น ๆ 3-5 ย่อหน้า โดยเน้นความหมายเชิงเทคนิคของ RSI, SMA20, SMA50, MACD histogram และสัญญาณที่มีให้ หากข้อมูลใดไม่พอให้กล่าวว่า 'ข้อมูลไม่พอ' สำหรับส่วนที่เกี่ยวข้อง ปิดท้ายด้วย disclaimer สั้น ๆ ว่าเป็นข้อมูลประกอบ ไม่ใช่คำแนะนำการลงทุน.";
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractAnalysisText(array $payload): ?string
    {
        $content = Arr::get($payload, 'content');

        if (is_string($content)) {
            return $content;
        }

        if (is_array($content)) {
            foreach ($content as $block) {
                if (! is_array($block)) {
                    continue;
                }

                if ((string) ($block['type'] ?? '') === 'text' && isset($block['text'])) {
                    return (string) $block['text'];
                }
            }
        }

        $completion = Arr::get($payload, 'completion');
        if (is_string($completion) && $completion !== '') {
            return $completion;
        }

        return null;
    }

    /**
     * @param float|int|string|null $value
     */
    private function formatNumber(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if (! is_numeric($value)) {
            return 'N/A';
        }

        return number_format((float) $value, 2);
    }

    /**
     * @param float|int|string|null $value
     */
    private function formatPercent(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if (! is_numeric($value)) {
            return 'N/A';
        }

        return number_format((float) $value, 2) . '%';
    }
}
