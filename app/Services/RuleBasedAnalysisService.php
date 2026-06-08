<?php
 
declare(strict_types=1);
 
namespace App\Services;
 
final class RuleBasedAnalysisService
{
    /**
     * @param array{
     *     symbol?: string,
     *     name?: ?string,
     *     close?: ?float,
     *     percent_change?: ?float,
     *     rsi?: ?float,
     *     sma20?: ?float,
     *     sma50?: ?float,
     *     macd_histogram?: ?float
     * } $data
     * @return array{ok: bool, summary: string, points: array<string>, error: ?string}
     */
    public function analyze(array $data): array
    {
        $symbol = (string) ($data['symbol'] ?? '');
        $close = $data['close'] ?? null;
        $percentChange = $data['percent_change'] ?? null;
        $rsi = $data['rsi'] ?? null;
        $sma50 = $data['sma50'] ?? null;
        $macdHistogram = $data['macd_histogram'] ?? null;
 
        $points = [];
 
        // ==================== RSI Analysis ====================
        if ($rsi !== null) {
            if ($rsi > 70.0) {
                $points[] = sprintf('RSI %.2f สูงกว่า 70 บ่งชี้ภาวะซื้อมากเกินไป (Overbought) อาจมีแรงขายทำกำไร', $rsi);
            } elseif ($rsi < 30.0) {
                $points[] = sprintf('RSI %.2f ต่ำกว่า 30 บ่งชี้ภาวะขายมากเกินไป (Oversold) อาจมีตัวซื้ออยู่', $rsi);
            } else {
                $points[] = sprintf('RSI %.2f อยู่ในระดับปกติ (30–70) ตลาดมีความสมดุลระหว่างผู้ซื้อและผู้ขาย', $rsi);
            }
        }
 
        // ==================== SMA50 Trend Analysis ====================
        if ($close !== null && $sma50 !== null) {
            if ($close > $sma50) {
                $points[] = sprintf('ราคา $%.2f อยู่เหนือเส้นค่าเฉลี่ย 50 วัน ($%.2f) บ่งชี้แนวโน้มกลาง–ยาวเป็นขาขึ้น (Uptrend)', $close, $sma50);
            } elseif ($close < $sma50) {
                $points[] = sprintf('ราคา $%.2f อยู่ต่ำกว่าเส้นค่าเฉลี่ย 50 วัน ($%.2f) บ่งชี้แนวโน้มกลาง–ยาวเป็นขาลง (Downtrend)', $close, $sma50);
            } else {
                $points[] = sprintf('ราคา $%.2f เท่ากับค่าเฉลี่ย 50 วัน อาจอยู่ในช่วงควบแน่น', $close);
            }
        }
 
        // ==================== MACD Analysis ====================
        if ($macdHistogram !== null) {
            if ($macdHistogram > 0.0) {
                $points[] = sprintf('MACD Histogram %.4f เป็นบวก โมเมนตัมระยะสั้นเป็นขาขึ้น (Bullish) อาจเห็นแรงซื้อเพิ่มขึ้น', $macdHistogram);
            } elseif ($macdHistogram < 0.0) {
                $points[] = sprintf('MACD Histogram %.4f เป็นลบ โมเมนตัมระยะสั้นเป็นขาลง (Bearish) อาจเห็นแรงขายเพิ่มขึ้น', $macdHistogram);
            } else {
                $points[] = 'MACD Histogram อยู่ที่ศูนย์ โมเมนตัมกำลังเปลี่ยนทิศทาง';
            }
        }
 
        // ==================== Price Change Analysis ====================
        if ($percentChange !== null) {
            if ($percentChange > 5.0) {
                $points[] = sprintf('ราคาเปลี่ยนแปลง +%.2f%% แรงขึ้นอย่างมาก ควรติดตามว่าจะรักษาแรงขึ้นได้หรือไม่', $percentChange);
            } elseif ($percentChange > 0.0) {
                $points[] = sprintf('ราคาเปลี่ยนแปลง +%.2f%% เป็นบวก แนวโน้มเป็นขาขึ้นในวันนี้', $percentChange);
            } elseif ($percentChange < -5.0) {
                $points[] = sprintf('ราคาเปลี่ยนแปลง %.2f%% แรงลงอย่างมาก ควรติดตามสัญญาณทางเทคนิคเพิ่มเติม', $percentChange);
            } elseif ($percentChange < 0.0) {
                $points[] = sprintf('ราคาเปลี่ยนแปลง %.2f%% เป็นลบ แนวโน้มเป็นขาลงในวันนี้', $percentChange);
            } else {
                $points[] = 'ราคาปิดเท่ากับการเปิดวันนี้ ตลาดมีความสมดุล';
            }
        }
 
        // ==================== Generate Summary ====================
        $summary = $this->generateSummary($rsi, $close, $sma50, $macdHistogram, $percentChange);
 
        return [
            'ok' => true,
            'summary' => $summary,
            'points' => $points,
            'error' => null,
        ];
    }
 
    private function generateSummary(?float $rsi, ?float $close, ?float $sma50, ?float $macdHistogram, ?float $percentChange): string
    {
        $trendSignal = '';
        $momentumSignal = '';
        $rsiSignal = '';
        $priceSignal = '';
 
        // Trend (from SMA50)
        if ($close !== null && $sma50 !== null) {
            $trendSignal = $close > $sma50 ? 'แนวโน้มกลาง–ยาวเป็นขาขึ้น' : 'แนวโน้มกลาง–ยาวเป็นขาลง';
        }
 
        // Momentum (from MACD)
        if ($macdHistogram !== null) {
            $momentumSignal = $macdHistogram > 0.0 ? 'โมเมนตัมระยะสั้นบวก' : 'โมเมนตัมระยะสั้นลบ';
        }
 
        // RSI Signal
        if ($rsi !== null) {
            if ($rsi > 70.0) {
                $rsiSignal = 'ภาวะซื้อมากเกินไป';
            } elseif ($rsi < 30.0) {
                $rsiSignal = 'ภาวะขายมากเกินไป';
            } else {
                $rsiSignal = 'ระดับปกติ';
            }
        }
 
        // Price Change Signal
        if ($percentChange !== null) {
            $priceSignal = $percentChange >= 0 ? 'บวก' : 'ลบ';
        }
 
        $parts = [];
        if ($trendSignal !== '') {
            $parts[] = $trendSignal;
        }
        if ($momentumSignal !== '') {
            $parts[] = $momentumSignal;
        }
        if ($rsiSignal !== '') {
            $parts[] = 'RSI ' . $rsiSignal;
        }
        if ($priceSignal !== '') {
            $parts[] = 'ปรับราคา ' . $priceSignal;
        }
 
        if (empty($parts)) {
            return 'ไม่มีข้อมูลเพียงพอสำหรับวิเคราะห์';
        }
 
        $combined = implode(', ', $parts);
 
        // Generate overall interpretation (เชิงการศึกษา ไม่ใช่คำแนะนำการลงทุน)
        if ($trendSignal !== '' && $momentumSignal !== '') {
            if (str_contains($trendSignal, 'ขึ้น') && str_contains($momentumSignal, 'บวก')) {
                return 'สัญญาณบวกที่แข็งแรง: ' . $combined . ' • ทั้งแนวโน้มกลาง–ยาวและโมเมนตัมระยะสั้นไปในทิศขาขึ้นพร้อมกัน';
            } elseif (str_contains($trendSignal, 'ขึ้น') && str_contains($momentumSignal, 'ลบ')) {
                return 'สัญญาณผสม: ' . $combined . ' • แนวโน้มยาวยังเป็นขาขึ้น แต่ระยะสั้นเริ่มอ่อนแรง ควรติดตามใกล้ชิด';
            } elseif (str_contains($trendSignal, 'ลง') && str_contains($momentumSignal, 'ลบ')) {
                return 'สัญญาณลบที่แข็งแรง: ' . $combined . ' • ทั้งแนวโน้มกลาง–ยาวและโมเมนตัมระยะสั้นไปในทิศขาลงพร้อมกัน';
            } elseif (str_contains($trendSignal, 'ลง') && str_contains($momentumSignal, 'บวก')) {
                return 'สัญญาณผสม: ' . $combined . ' • แนวโน้มยาวยังเป็นขาลง แต่ระยะสั้นเริ่มมีแรงฟื้น ติดตามสัญญาณเพิ่มเติม';
            }
        }
 
        return 'ภาพรวมสัญญาณ: ' . $combined;
    }
}
 