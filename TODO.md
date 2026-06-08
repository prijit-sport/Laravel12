# TODO

- [x] เพิ่ม `describeCompany()` ใน `app/Services/OllamaAnalysisService.php` (Ollama /api/chat + cache 1 วัน)
- [x] เพิ่ม endpoint `companyInfo()` ใน `app/Http/Controllers/StockController.php` (คืน JSON)
- [x] เพิ่ม route `GET /stock/{symbol}/company-info` ใน `routes/web.php`
- [x] ปรับหน้า `resources/views/stock/show_phase5.blade.php`
  - [x] เปลี่ยน header/title/subtitle ให้ทางการ
  - [x] เพิ่มการ์ด “เกี่ยวกับบริษัท” โหลด async อัตโนมัติ
  - [x] แสดง spinner, แสดงผล/handle error แบบสุภาพ
  - [x] ติดป้าย disclaimer ว่าข้อมูลจาก AI เพื่อการศึกษา
- [x] รัน `php artisan cache:clear` และทดสอบเปิด `/stock/NVDA`, `/stock/KO`, `/stock/JPM`
