# Dự án Zyro - Hướng Dẫn Triển Khai (Deployment Guide)

Tài liệu này ghi chú các thiết lập quan trọng trên Server khi Deploy dự án Zyro lên môi trường Production (VPS/Server thật).

---

## 1. Cài đặt Cron Job (Lên Lịch Tác Vụ Tự Động)

Dự án Zyro sử dụng tính năng **Task Scheduling** của Laravel (cấu hình trong `backend/routes/console.php`) để thực hiện các công việc chạy ngầm tự động (Ví dụ: Đồng bộ trạng thái đơn hàng từ Đơn vị vận chuyển, thu thập dữ liệu hàng ngày, gửi email sinh nhật...).

⚠️ **BẮT BUỘC:** Khi đưa lên Server (Ubuntu/CentOS), bạn **chỉ cần thiết lập duy nhất 1 dòng lệnh Cron** cho toàn bộ hệ thống Laravel.

### Hướng dẫn thiết lập:
1. SSH vào server.
2. Mở file crontab bằng lệnh:
   ```bash
   crontab -e
   ```
3. Thêm dòng sau vào cuối file (Nhớ thay `/path/to/zyro/backend` bằng đường dẫn thực tế đến thư mục backend của bạn trên Server):
   ```bash
   * * * * * cd /path/to/zyro/backend && php artisan schedule:run >> /dev/null 2>&1
   ```
   *Giải thích: Lệnh này sẽ chạy mỗi phút 1 lần. Laravel sẽ tự động kiểm tra xem có tác vụ nào đến giờ chạy hay chưa (ví dụ tác vụ đồng bộ ĐVVC được cấu hình `hourly()` thì đúng 1 tiếng Laravel mới cho phép chạy 1 lần).*

### Danh sách các Task Tự Động hiện có:
- `php artisan zyro:sync-shipping`: Tự động kiểm tra trạng thái đơn hàng (Đang giao -> Thành công/Hoàn trả) bằng cách gọi API của ĐVVC (GHN, GHTK...). Được cấu hình chạy 1 tiếng / 1 lần (`->hourly()`).

---

## 2. Cài đặt Queue Worker (Xử Lý Hàng Đợi)

Hệ thống Zyro sử dụng Queue để xử lý các tác vụ nặng (như gửi Email thông báo đơn hàng mới, gửi Email reset mật khẩu) để không làm treo trang web của người dùng.

⚠️ **BẮT BUỘC:** Phải duy trì tiến trình Worker chạy ngầm vĩnh viễn trên Server bằng công cụ **Supervisor**.

### Lệnh chạy thủ công (Để test):
```bash
cd /path/to/zyro/backend
php artisan queue:work
```

### Cấu hình Supervisor (Dành cho Production):
Tạo file cấu hình `/etc/supervisor/conf.d/zyro-worker.conf`:
```ini
[program:zyro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/zyro/backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/zyro/backend/storage/logs/worker.log
stopwaitsecs=3600
```
Sau đó kích hoạt Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start zyro-worker:*
```

---

*Lưu ý: Mọi cấu hình liên quan đến API Vận Chuyển sẽ được điều chỉnh trong `backend/app/Services/Shipping`.*
