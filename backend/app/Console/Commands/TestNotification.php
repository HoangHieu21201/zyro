<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Notifications\AdminAlertNotification;

class TestNotification extends Command
{
    /**
     * Cú pháp gọi lệnh: php artisan test:noti 1
     */
    protected $signature = 'test:noti {admin_id? : ID của Admin nhận thông báo (Mặc định lấy ID 1)}';

    protected $description = 'Bắn thử Real-time Notification qua Reverb cho Admin';

    public function handle()
    {
        $adminId = $this->argument('admin_id') ?? 1;
        $admin = Admin::find($adminId);

        if (!$admin) {
            $this->error("Không tìm thấy Admin với ID: {$adminId}");
            return;
        }

        // Tạo mảng dữ liệu ảo để test
        $fakeData = [
            'type'    => 'warning', // success, warning, danger, info
            'title'   => 'Yêu cầu hoàn trả mới!',
            'message' => 'Khách hàng vừa gửi yêu cầu hoàn trả cho đơn hàng #ZYRO-9999. Vui lòng kiểm tra.',
            'url'     => '/admin/returns'
        ];

        // Gửi notification
        $admin->notify(new AdminAlertNotification($fakeData));

        $this->info("✅ Đã bắn thông báo Real-time cho Admin: {$admin->fullname} (ID: {$admin->id})");
        $this->line("Dữ liệu: " . json_encode($fakeData, JSON_UNESCAPED_UNICODE));
    }
}