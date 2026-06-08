# TODO - เฟส 8: ตัด rate limit จาก Twelve Data quote (compare/watchlist)

## เป้าหมาย
- ให้หน้า /compare และ /watchlist คำนวณ "ราคาล่าสุด" และ "%เปลี่ยนแปลง" จาก DB (`stock_prices`) แทนการเรียก Twelve Data quote API

## Steps
- [x] 1) อัปเดต `app/Services/StockService.php`: เพิ่ม method `getLatestFromDb()` อ่าน 2 แถวล่าสุดแล้วคำนวณ percent change
- [x] 2) อัปเดต `app/Http/Controllers/CompareController.php`: เรียก `getTimeSeries()` ก่อน แล้วอ่าน close/% จาก `getLatestFromDb()` (ไม่เรียก quote)
- [x] 3) อัปเดต `app/Http/Controllers/WatchlistController.php`: ทำเช่นเดียวกับ Compare (ไม่เรียก quote)
- [x] 4) รัน `php artisan cache:clear`
- [ ] 5) ทดสอบ: `/stock/AVGO` เพื่อ populate DB แล้วเปิด `/compare` และ `/watchlist` และ refresh ซ้ำ (คาดว่าไม่ชน rate limit และ % คำนวณจาก DB)



