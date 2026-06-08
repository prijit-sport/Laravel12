<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    /**
     * ฟังก์ชันหลักสำหรับรับคำถามและส่งไปให้ Ollama (Local AI) ประมวลผล
     */
    public function askAi(Request $request)
    {
        // 1. รับข้อความคำถามจาก Request (ถ้าไม่ได้ส่งมา จะใช้ข้อความทักทายเริ่มต้น)
        $prompt = $request->input('prompt', 'สวัสดี คุณช่วยอะไรฉันได้บ้างเกี่ยวกับการจองสนามฟุตบอล?');
        
        // 2. กำหนดชื่อโมเดลที่ต้องการใช้งาน (ต้องตรงกับที่โหลดไว้ในเครื่อง)
        $model = 'qwen2'; 

        try {
            // 3. ยิง HTTP POST Request ไปยัง Ollama API ในเครื่อง (พอร์ต 11434)
            // ตั้งค่า timeout 60 วินาที ป้องกันปัญหาคำถามยากแล้ว AI ตอบช้า
            $response = Http::timeout(60)->post('http://127.0.0.1:11434/api/generate', [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false, // false = ให้ AI คิดจนเสร็จก่อน ค่อยส่งคำตอบกลับมาทีเดียว (ง่ายต่อการเขียนโค้ดฝั่งเว็บ)
            ]);

            // 4. ตรวจสอบว่าคำสั่งสำเร็จหรือไม่ (HTTP Status 200 OK)
            if ($response->successful()) {
                $result = $response->json();
                
                return response()->json([
                    'success' => true,
                    'answer' => $result['response'] // ข้อความที่ AI ตอบกลับมา
                ]);
            }

            // กรณีเชื่อมต่อได้ แต่ Ollama แจ้ง Error กลับมา (เช่น หาโมเดลไม่เจอ)
            Log::error('Ollama API Error: ' . $response->body());
            return response()->json([
                'success' => false,
                'error' => 'Ollama API แจ้งข้อผิดพลาด: ' . $response->status()
            ], $response->status());

        } catch (\Exception $e) {
            // กรณีเกิดข้อผิดพลาดในการเชื่อมต่อ (เช่น ลืมรันโปรแกรม Ollama)
            Log::error('Ollama Connection Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'ไม่สามารถเชื่อมต่อกับ AI ในเครื่องได้ กรุณาตรวจสอบว่าโปรแกรม Ollama รันอยู่หรือไม่'
            ], 500);
        }
    }
}