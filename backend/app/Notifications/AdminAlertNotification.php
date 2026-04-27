<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;

class AdminAlertNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => $this->data['type'] ?? 'info',
            'title'   => $this->data['title'] ?? 'Thông báo hệ thống',
            'message' => $this->data['message'] ?? '',
            'url'     => $this->data['url'] ?? '#',
            'time'    => now()->toDateTimeString(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'   => $this->id,
            'data' => $this->toArray($notifiable),
            'read_at' => null,
            'created_at' => now(),
        ]);
    }

    // ĐÃ FIX CỰC KỲ QUAN TRỌNG: Ép Laravel đổi tên sự kiện phát sóng thành .AdminAlert để Vue bắt được
    public function broadcastAs(): string
    {
        return 'AdminAlert';
    }
}