<?php
 
declare(strict_types=1);
 
namespace App\Services;
 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
 
final class OllamaAnalysisService
{
    private const CACHE_TTL_SECONDS = 60 * 60;
    private const COMPANY_CACHE_TTL_SECONDS = 60 * 1440;

    private const GROQ_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';
    private const GROQ_MODEL = 'llama3-8b-8192';

 
    /**
     * Analyze stock technical data using Ollama LLM.
     *
     * @param  array<string, mixed>  $data  ข้อมูลหุ้น: symbol, name, close, percent_change, rsi, sma50, macd_histogram
     * @return array{ok: bool, analysis: ?string, error: ?string}
     */
    public function analyze(array $data): array
    {
        $baseUrl = (string) env('OLLAMA_BASE_URL', 'http://localhost:11434');
        $model   = (string) env('OLLAMA_MODEL', 'qwen2');

        $groqKey = (string) env('GROQ_API_KEY', '');

        $symbol       = strtoupper((string) ($data['symbol'] ?? ''));

        $symbolForKey = $symbol !== '' ? $symbol : 'UNKNOWN';
        $url          = self::GROQ_ENDPOINT;

        if ($groqKey === '') {
            return ['ok' => false, 'analysis' => null, 'error' => 'Missing GROQ_API_KEY.'];
        }
 
        if ($symbol === '') {
            return ['ok' => false, 'analysis' => null, 'error' => 'Missing stock symbol.'];
        }
 
        $payloadForCache = [
            'symbol'          => $symbolForKey,
            'name'            => $data['name'] ?? null,
            'close'           => $data['close'] ?? null,
            'percent_change'  => $data['percent_change'] ?? null,
            'rsi'             => $data['rsi'] ?? null,
            'sma50'           => $data['sma50'] ?? null,
            'macd_histogram'  => $data['macd_histogram'] ?? null,
        ];
 
        $cacheKey = $this->cacheKey($model, $symbolForKey, $payloadForCache);
 
        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($url, $model, $data): array {
            try {
                $systemPrompt = 'คุณเป็นผู้ให้ความรู้ด้านการลงทุน อธิบาย "สัญญาณที่ตีความแล้ว" เป็นภาษาไทยที่เข้าใจง่ายและกระชับ ห้ามแนะนำให้ซื้อหรือขาย อ้างอิงเฉพาะตัวเลขที่ให้มาเท่านั้น และปิดท้ายด้วยข้อความเตือนสั้น ๆ ว่าเป็นข้อมูลเพื่อการศึกษา';
                $userMessage  = $this->buildUserMessage($data);
 
                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $groqKey,
                        'Content-Type'  => 'application/json',
                    ])
                    ->post($url, [
                        'model'       => self::GROQ_MODEL,
                        'messages'    => [
                            ['role' => 'user', 'content' => $systemPrompt . "\n\n" . $userMessage],
                        ],
                        'temperature' => 0.7,
                        'max_tokens'  => 1024,
                    ]);

 
                if ($response->failed()) {
                    $status    = $response->status();
                    $lowerBody = mb_strtolower((string) $response->body());
 
                    if (str_contains($lowerBody, 'model') && str_contains($lowerBody, 'not found')) {
                        return ['ok' => false, 'analysis' => null,
                            'error' => 'ไม่พบโมเดลใน Ollama โปรดลองดึงโมเดล (เช่น ollama pull ' . $model . ') แล้วลองใหม่'];
                    }
 
                    return ['ok' => false, 'analysis' => null, 'error' => 'Ollama API error: ' . $status];
                }
 
                $payload  = $response->json();
                $analysis = is_array($payload) ? $this->extractText($payload) : null;

                if (!is_string($analysis) || trim($analysis) === '') {
                    $fallback = is_array($payload) ? ($payload['choices'][0]['message']['content'] ?? null) : null;
                    $analysis = is_string($fallback) ? $fallback : null;
                }

 
                if ($analysis === null || trim($analysis) === '') {
                    return ['ok' => false, 'analysis' => null, 'error' => 'ไม่ได้รับคำตอบจาก Ollama'];
                }
 
                return ['ok' => true, 'analysis' => trim($analysis), 'error' => null];
 
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return ['ok' => false, 'analysis' => null,
                    'error' => 'ไม่สามารถเชื่อมต่อ Ollama ได้ (ตรวจสอบว่าเปิดโปรแกรม Ollama อยู่)'];
            } catch (\Throwable $e) {
                $msg = mb_strtolower((string) $e->getMessage());
                if (str_contains($msg, 'timed out') || str_contains($msg, 'timeout')) {
                    return ['ok' => false, 'analysis' => null, 'error' => 'โมเดลตอบช้ามาก ลองใหม่อีกครั้ง'];
                }
                return ['ok' => false, 'analysis' => null, 'error' => 'เกิดข้อผิดพลาดในการวิเคราะห์ด้วย Ollama'];
            }
        });
    }
 
    /**
     * Describe a company in Thai using Ollama LLM.
     *
     * @param  string       $symbol  Ticker symbol e.g. "NVDA"
     * @param  string|null  $name    Company name (optional)
     * @return array{ok: bool, description: ?string, error: ?string}
     */
    public function describeCompany(string $symbol, ?string $name = null): array
    {
        $baseUrl   = (string) env('OLLAMA_BASE_URL', 'http://localhost:11434');
        $model     = (string) env('OLLAMA_MODEL', 'qwen2');
        $symbolOut = strtoupper(trim($symbol));

        $groqKey = (string) env('GROQ_API_KEY', '');

        if ($groqKey === '') {
            return ['ok' => false, 'description' => null, 'error' => 'Missing GROQ_API_KEY.'];
        }

        $url = self::GROQ_ENDPOINT;


 
        if ($symbolOut === '') {
            return ['ok' => false, 'description' => null, 'error' => 'Missing stock symbol.'];
        }

        $cacheKey = sprintf('ollama:company:%s:%s', $model, $symbolOut);

 
        return Cache::remember($cacheKey, self::COMPANY_CACHE_TTL_SECONDS, function () use ($url, $model, $symbolOut, $name): array {
            try {
                $systemPrompt = 'คุณเป็นผู้ให้ข้อมูลบริษัท อธิบายสั้น ๆ กระชับ (2–3 ประโยค) เป็นภาษาไทยว่าบริษัทนี้ทำธุรกิจเกี่ยวกับอะไร ในอุตสาหกรรมใด ตอบเฉพาะข้อเท็จจริงที่มั่นใจ ห้ามแต่งข้อมูลที่ไม่รู้';
                $companyName  = ($name !== null && trim($name) !== '') ? trim($name) : $symbolOut;
                $userMessage  = sprintf('บริษัท %s (สัญลักษณ์ %s) ทำธุรกิจเกี่ยวกับอะไร', $companyName, $symbolOut);
 
                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $groqKey,
                        'Content-Type'  => 'application/json',
                    ])
                    ->post(self::GROQ_ENDPOINT, [
                        'model'       => self::GROQ_MODEL,
                        'messages'    => [
                            ['role' => 'user', 'content' => $systemPrompt . "\n\n" . $userMessage],
                        ],
                        'temperature' => 0.7,
                        'max_tokens'  => 1024,
                    ]);

 
                if ($response->failed()) {
                    return ['ok' => false, 'description' => null,
                        'error' => 'Ollama ตอบกลับไม่สำเร็จ โปรดตรวจสอบการตั้งค่า'];
                }
 
                $payload = $response->json();
                $text    = is_array($payload) ? $this->extractText($payload) : null;
 
                if ($text === null || trim($text) === '') {
                    return ['ok' => false, 'description' => null, 'error' => 'ไม่ได้รับคำตอบจาก Ollama'];
                }
 
                return ['ok' => true, 'description' => trim($text), 'error' => null];
 
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return ['ok' => false, 'description' => null,
                    'error' => 'ไม่สามารถเชื่อมต่อ Ollama ได้ (ตรวจสอบว่าเปิดโปรแกรม Ollama อยู่)'];
            } catch (\Throwable $e) {
                $msg = mb_strtolower((string) $e->getMessage());
                if (str_contains($msg, 'timed out') || str_contains($msg, 'timeout')) {
                    return ['ok' => false, 'description' => null, 'error' => 'โมเดลตอบช้ามาก ลองใหม่อีกครั้ง'];
                }
                return ['ok' => false, 'description' => null,
                    'error' => 'เกิดข้อผิดพลาดในการสร้างคำอธิบายบริษัทด้วย Ollama'];
            }
        });
    }
 
    /**
     * Build an interpreted user message with pre-computed signals for the LLM.
     *
     * @param  array<string, mixed>  $data  ข้อมูลหุ้น: symbol, name, close, percent_change, rsi, sma50, macd_histogram
     */
    private function buildUserMessage(array $data): string
    {
        $symbol        = strtoupper((string) ($data['symbol'] ?? ''));
        $name          = $data['name'] ?? null;
        $close         = isset($data['close']) ? (float) $data['close'] : null;
        $percentChange = isset($data['percent_change']) ? (float) $data['percent_change'] : null;
        $rsi           = isset($data['rsi']) ? (float) $data['rsi'] : null;
        $sma50         = isset($data['sma50']) ? (float) $data['sma50'] : null;
        $macdHistogram = isset($data['macd_histogram']) ? (float) $data['macd_histogram'] : null;
 
        $rsiVal       = $rsi !== null ? round($rsi, 2) : null;
        $sma50Val     = $sma50 !== null ? round($sma50, 2) : null;
        $macdHistVal  = $macdHistogram !== null ? round($macdHistogram, 4) : null;
 
        // ตีความ RSI
        $rsiLabel = 'อยู่ในโซนกลาง (30–70) ยังไม่มีสัญญาณซื้อ/ขายชัดเจน';
        if ($rsiVal !== null) {
            if ($rsiVal > 70.0) {
                $rsiLabel = 'อยู่ในโซน Overbought (>70) ซื้อมากเกินไป อาจมีแรงขายทำกำไร';
            } elseif ($rsiVal < 30.0) {
                $rsiLabel = 'อยู่ในโซน Oversold (<30) ขายมากเกินไป อาจมีแรงซื้อกลับ';
            }
        }
 
        // ตีความ Trend
        $trendLabel = 'ไม่มีข้อมูล SMA50';
        if ($close !== null && $sma50Val !== null) {
            $trendLabel = $close > $sma50Val
                ? 'ราคา (' . $this->fmt($close) . ') อยู่เหนือ SMA50 (' . $this->fmt($sma50Val) . ') = แนวโน้มขาขึ้น'
                : 'ราคา (' . $this->fmt($close) . ') อยู่ต่ำกว่า SMA50 (' . $this->fmt($sma50Val) . ') = แนวโน้มขาลง';
        }
 
        // ตีความ MACD
        $macdLabel = $macdHistVal !== null
            ? ($macdHistVal > 0.0
                ? 'Bullish (โมเมนตัมบวก) แรงซื้อยังแข็งแกร่ง'
                : 'Bearish (โมเมนตัมลบ) แรงขายครอบงำ')
            : 'ไม่มีข้อมูล';
 
        $stockName   = ($name !== null && trim((string) $name) !== '') ? trim((string) $name) : $symbol;
        $closeStr    = $close !== null ? $this->fmt($close) : 'N/A';
        $pctStr      = $percentChange !== null ? number_format($percentChange, 2) . '%' : 'N/A';
        $rsiStr      = $rsiVal !== null ? (string) $rsiVal : 'N/A';
        $macdHistStr = $macdHistVal !== null ? (string) $macdHistVal : 'N/A';
 
        $msg  = 'หุ้น: ' . $symbol . ' (' . $stockName . ')' . "\n";
        $msg .= 'ราคาปัจจุบัน: ' . $closeStr . ' (' . $pctStr . ')' . "\n\n";
        $msg .= 'สัญญาณทางเทคนิค (ตีความแล้ว):' . "\n";
        $msg .= '- RSI (14) = ' . $rsiStr . ' → ' . $rsiLabel . "\n";
        $msg .= '- แนวโน้ม: ' . $trendLabel . "\n";
        $msg .= '- MACD Histogram = ' . $macdHistStr . ' → ' . $macdLabel . "\n\n";
        $msg .= 'โปรดอธิบายสัญญาณข้างต้นเป็นภาษาไทยที่เข้าใจง่าย 3–4 ประโยค สรุปภาพรวมเชิงเทคนิค ห้ามแนะนำซื้อหรือขาย';
 
        return $msg;
    }
 
    /**
     * Extract text content from the Ollama /api/chat response payload.
     *
     * @param  array<string, mixed>  $payload
     */
    private function extractText(array $payload): ?string
    {
        $content = $payload['message']['content'] ?? null;
        return is_string($content) ? $content : null;
    }
 
    /**
     * Build a cache key for the analysis result.
     *
     * @param  array<string, mixed>  $payloadForCache
     */
    private function cacheKey(string $model, string $symbol, array $payloadForCache): string
    {
        $hash = hash('sha256', json_encode($payloadForCache, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return sprintf('ollama:%s:%s:%s', $model, $symbol, $hash);
    }
 
    /**
     * Format a numeric value to 2 decimal places.
     *
     * @param  float|int|string|null  $value
     */
    private function fmt(mixed $value): string
    {
        if ($value === null || !is_numeric($value)) {
            return 'N/A';
        }
        return number_format((float) $value, 2);
    }
}
