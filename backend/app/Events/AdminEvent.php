<?php

// File: backend/app/Events/AdminEvent.php

namespace App\Events;

use App\Models\Admin;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Admin $admin;

    public function __construct(string $action, Admin $admin)
    {
        $this->action = $action;
        $this->admin = $admin;
    }

    public function broadcastOn(): array
    {
        // Sử dụng một channel Private riêng biệt cho Admin (Nhớ đăng ký trong channels.php)
        return [
            new PrivateChannel('admin.admins'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AdminEvent';
    }

    public function broadcastWith(): array
    {
        // Đảm bảo luôn load kèm Role để Frontend có dữ liệu hiển thị (tên chức vụ, màu sắc)
        if (!$this->admin->relationLoaded('role')) {
            $this->admin->load('role');
        }

        return [
            'action' => $this->action,
            'admin'  => $this->admin->toArray(),
        ];
    }
}