<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class OllamaAnalysisService
{
    private const CACHE_TTL_SECONDS = 60 * 60;
    private const COMPANY_CACHE_TTL_SECONDS = 60 * 1440;

    // @phpstan-ignore-next-line
    // (IntelliSense error on complex array-shape generics in this editor; logic is correct.)

    /**
     * @param array{symbol?: string, name?: ?string, close?: ?float, percent_change?: ?float, rsi?: ?float, sma50?: ?float, macd_histogram?: ?float} $data
     * @return array{ok: bool, analysis: ?string, error: ?string}
     */
    public function analyze(array $data): array
    {
        $baseUrl = (string) env('OLLAMA_BASE_URL', 'http://localhost:11434');
        $model = (string) env('OLLAMA_MODEL', 'qwen2');

        $symbol = strtoupper((string) ($data['symbol'] ?? ''));
        $symbolForKey = $symbol !== '' ? $symbol : 'UNKNOWN';

        $url = rtrim($baseUrl, '/') . '/api/chat';

        if ($symbol === '') {
            return [
                'ok' => false,
                'analysis' => null,
                'error' => 'Missing stock symbol.',
            ];
        }

        $payloadForCache = [
            'symbol' => $symbolForKey,
            'name' => $data['name'] ?? null,
            'close' => $data['close'] ?? null,
            'percent_change' => $data['percent_change'] ?? null,
            'rsi' => $data['rsi'] ?? null,
            'sma50' => $data['sma50'] ?? null,
            'macd_histogram' => $data['macd_histogram'] ?? null,
        ];

        $cacheKey = $this->cacheKey($model, $symbolForKey, $payloadForCache);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($url, $model, $data): array {
            try {
                $systemPrompt = 'คุณเป็นผู้ให้ความรู้ด้านการลงทุน อธิบาย indicator ที่ได้รับเป็นภาษาไทยที่เข้าใจง่ายและกระชับ อ้างอิงเฉพาะตัวเลขที่ให้มาเท่านั้น ห้ามแนะนำให้ซื้อหรือขาย ปิดท้ายด้วยข้อความเตือนสั้น ๆ ว่าเป็นข้อมูลเพื่อการศึกษา';

                $userMessage = $this->buildUserMessage($data);


                $response = Http::timeout(180)->post($url, [
                    'model' => $model,
                    'stream' => false,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage . "\n\nช่วยสรุปวิเคราะห์เชิงเทคนิคเป็นภาษาไทย"],
                    ],
                    'options' => [
                        'temperature' => 0.4,
                        'num_predict' => 400,
                    ],
                ]);

                if ($response->failed()) {
                    $status = $response->status();
                    $body = (string) $response->body();

                    $lowerBody = mb_strtolower($body);

                    if (str_contains($lowerBody, 'model') && str_contains($lowerBody, 'not found')) {
                        return [
                            'ok' => false,
                            'analysis' => null,
                            'error' => 'ไม่พบโมเดลใน Ollama โปรดลองดึงโมเดล (เช่น ollama pull ' . $model . ') แล้วลองใหม่',
                        ];
                    }

                    if (str_contains($lowerBody, '404') || $status === 404) {
                        return [
                            'ok' => false,
                            'analysis' => null,
                            'error' => 'Ollama ตอบกลับไม่สำเร็จ โปรดลองตรวจสอบการตั้งค่าและความพร้อมของบริการ',
                        ];
                    }

                    return [
                        'ok' => false,
                        'analysis' => null,
                        'error' => 'Ollama API error: ' . $status,
                    ];
                }

                $payload = $response->json();
                if (! is_array($payload)) {
                    return [
                        'ok' => false,
                        'analysis' => null,
                        'error' => 'Ollama ตอบกลับในรูปแบบที่ไม่คาดคิด',
                    ];
                }

                $analysis = $this->extractAnalysisText($payload);
                if ($analysis === null) {
                    return [
                        'ok' => false,
                        'analysis' => null,
                        'error' => 'ไม่ได้รับคำตอบจาก Ollama',
                    ];
                }

                $analysisTrimmed = trim($analysis);
                if ($analysisTrimmed === '') {
                    return [
                        'ok' => false,
                        'analysis' => null,
                        'error' => 'Ollama ตอบกลับเป็นข้อความว่าง',
                    ];
                }

                return [
                    'ok' => true,
                    'analysis' => $analysisTrimmed,
                    'error' => null,
                ];
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return [
                    'ok' => false,
                    'analysis' => null,
                    'error' => 'ไม่สามารถเชื่อมต่อ Ollama ได้ (ตรวจสอบว่าเปิดโปรแกรม Ollama อยู่)',
                ];
            } catch (\Illuminate\Http\Client\RequestException $e) {
                return [
                    'ok' => false,
                    'analysis' => null,
                    'error' => 'Ollama วิเคราะห์ไม่สำเร็จ โปรดลองใหม่ภายหลัง',
                ];
            } catch (\Throwable $e) {
                $msg = (string) $e->getMessage();
                $lowerMsg = mb_strtolower($msg);

                if (str_contains($lowerMsg, 'timed out') || str_contains($lowerMsg, 'timeout')) {
                    return [
                        'ok' => false,
                        'analysis' => null,
                        'error' => 'โมเดลตอบช้ามาก ลองใหม่อีกครั้ง',
                    ];
                }

                return [
                    'ok' => false,
                    'analysis' => null,
                    'error' => 'เกิดข้อผิดพลาดในการวิเคราะห์ด้วย Ollama',
                ];
            }
        });
    }

    /**
     * @param array{symbol?: string, name?: ?string, close?: ?float, percent_change?: ?float, rsi?: ?float, sma50?: ?float, macd_histogram?: ?float} $data
     */
    private function buildUserMessage(array $data): string
    {
        $symbol = (string) ($data['symbol'] ?? '');
        $name = $data['name'] ?? null;
        $close = $data['close'] ?? null;
        $percentChange = $data['percent_change'] ?? null;
        $rsi = $data['rsi'] ?? null;
        $sma50 = $data['sma50'] ?? null;
        $macdHistogram = $data['macd_histogram'] ?? null;

        $lines = [];
        $lines[] = 'symbol: ' . $symbol;
        $lines[] = 'name: ' . ($name !== null && $name !== '' ? (string) $name : '-');
        $lines[] = 'close: ' . $this->formatNumber($close);
        $lines[] = 'percent_change: ' . $this->formatPercent($percentChange);
        $lines[] = 'RSI: ' . $this->formatNumber($rsi);
        $lines[] = 'SMA50: ' . $this->formatNumber($sma50);
        $lines[] = 'MACD histogram: ' . $this->formatNumber($macdHistogram);

        return implode("\n", $lines);
    }

    /**
     * @param mixed $payload
     */
    private function extractAnalysisText(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        $messageContent = $payload['message']['content'] ?? null;
        if (is_string($messageContent)) {
            return $messageContent;
        }

        return null;
    }

    /**
     * @param array{symbol?: string, name?: ?string, close?: ?float, percent_change?: ?float, rsi?: ?float, sma50?: ?float, macd_histogram?: ?float} $data
     */
    public function describeCompany(string $symbol, ?string $name = null): array
    {
        $baseUrl = (string) env('OLLAMA_BASE_URL', 'http://localhost:11434');
        $model = (string) env('OLLAMA_MODEL', 'qwen2');

        $symbolOut = strtoupper(trim($symbol));
        if ($symbolOut === '') {
            return [
                'ok' => false,
                'description' => null,
                'error' => 'Missing stock symbol.',
            ];
        }

        $url = rtrim($baseUrl, '/') . '/api/chat';

        $cacheKey = sprintf('ollama:company:%s:%s', $model, $symbolOut);

        return Cache::remember($cacheKey, self::COMPANY_CACHE_TTL_SECONDS, function () use ($url, $model, $symbolOut, $name): array {
            try {
                $systemPrompt = 'คุณเป็นผู้ให้ข้อมูลบริษัท อธิบายสั้น ๆ กระชับ (2–3 ประโยค) เป็นภาษาไทยว่าบริษัทนี้ทำธุรกิจเกี่ยวกับอะไร ในอุตสาหกรรมใด ตอบเฉพาะข้อเท็จจริงที่มั่นใจ ห้ามแต่งข้อมูลที่ไม่รู้';

                $companyName = $name !== null && trim($name) !== '' ? trim($name) : $symbolOut;
                $userMessage = sprintf('บริษัท %s (สัญลักษณ์ %s) ทำธุรกิจเกี่ยวกับอะไร', $companyName, $symbolOut);

                $response = Http::timeout(180)->post($url, [
                    'model' => $model,
                    'stream' => false,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'options' => [
                        'temperature' => 0.3,
                        'num_predict' => 200,
                    ],
                ]);

                if ($response->failed()) {
                    return [
                        'ok' => false,
                        'description' => null,
                        'error' => 'Ollama ตอบกลับไม่สำเร็จ โปรดลองตรวจสอบการตั้งค่าและความพร้อมของบริการ',
                    ];
                }

                $payload = $response->json();
                if (! is_array($payload)) {
                    return [
                        'ok' => false,
                        'description' => null,
                        'error' => 'Ollama ตอบกลับในรูปแบบที่ไม่คาดคิด',
                    ];
                }

                $text = $this->extractAnalysisText($payload);
                if ($text === null) {
                    return [
                        'ok' => false,
                        'description' => null,
                        'error' => 'ไม่ได้รับคำตอบจาก Ollama',
                    ];
                }

                $trimmed = trim($text);
                if ($trimmed === '') {
                    return [
                        'ok' => false,
                        'description' => null,
                        'error' => 'Ollama ตอบกลับเป็นข้อความว่าง',
                    ];
                }

                return [
                    'ok' => true,
                    'description' => $trimmed,
                    'error' => null,
                ];
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return [
                    'ok' => false,
                    'description' => null,
                    'error' => 'ไม่สามารถเชื่อมต่อ Ollama ได้ (ตรวจสอบว่าเปิดโปรแกรม Ollama อยู่)',
                ];
            } catch (\Illuminate\Http\Client\RequestException $e) {
                return [
                    'ok' => false,
                    'description' => null,
                    'error' => 'Ollama สร้างคำอธิบายไม่สำเร็จ โปรดลองใหม่ภายหลัง',
                ];
            } catch (\Throwable $e) {
                $msg = (string) $e->getMessage();
                $lowerMsg = mb_strtolower($msg);

                if (str_contains($lowerMsg, 'timed out') || str_contains($lowerMsg, 'timeout')) {
                    return [
                        'ok' => false,
                        'description' => null,
                        'error' => 'โมเดลตอบช้ามาก ลองใหม่อีกครั้ง',
                    ];
                }

                return [
                    'ok' => false,
                    'description' => null,
                    'error' => 'เกิดข้อผิดพลาดในการสร้างคำอธิบายบริษัทด้วย Ollama',
                ];
            }
        });
    }

    /**
     * @param array<string, mixed> $payloadForCache
     */
    private function cacheKey(string $model, string $symbol, array $payloadForCache): string
    {
        $hash = hash('sha256', json_encode($payloadForCache, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return sprintf('ollama:%s:%s:%s', $model, $symbol, $hash);
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

