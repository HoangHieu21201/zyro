<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientHomeUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
        // Không cần truyền data gì nặng nề, chỉ cần báo hiệu
    }

    public function broadcastOn(): array
    {
        // ĐÃ SỬA THÀNH CHANNEL PUBLIC (KHÔNG PHẢI PRIVATE)
        return [
            new Channel('client.home'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'HomeUpdated';
    }
}