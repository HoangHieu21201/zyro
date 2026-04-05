<?php

// File: backend/app/Events/RoleEvent.php

namespace App\Events;

use App\Models\Role;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Role $role;

    /**
     * Khởi tạo Event với 2 tham số được truyền từ Controller
     * @param string $action ('created', 'updated', 'deleted', 'restored')
     * @param Role $role (Model Role vừa bị tác động)
     */
    public function __construct(string $action, Role $role)
    {
        $this->action = $action;
        $this->role = $role;
    }

    /**
     * Khai báo kênh (Channel) mà event này sẽ được phát sóng vào.
     * Sử dụng PrivateChannel để bắt buộc user phải có token Sanctum (đã đăng nhập) mới nghe được.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.roles'),
        ];
    }

    /**
     * Đổi tên Event bắn xuống Client (Frontend sẽ lắng nghe tên này)
     * Rút gọn tên lại cho dễ gọi thay vì dùng namespace dài dòng của class.
     */
    public function broadcastAs(): string
    {
        return 'RoleEvent';
    }

    /**
     * Kiểm soát Payload (Dữ liệu) ném xuống WebSockets.
     * Cực kỳ quan trọng để Frontend nhận được đúng định dạng và tránh rò rỉ dữ liệu nhạy cảm.
     */
    public function broadcastWith(): array
    {
        // Kiểm tra load đếm số lượng admins (tránh lỗi nếu collection chưa load)
        if (!$this->role->relationLoaded('admins_count') && $this->action !== 'deleted') {
            $this->role->loadCount('admins');
        }

        return [
            'action' => $this->action,
            'role'   => $this->role->toArray(),
        ];
    }
}