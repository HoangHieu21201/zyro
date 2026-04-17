<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type; // 'variant' hoặc 'lookbook'
    public array $data;  // Dữ liệu mới nhất

    public function __construct(string $type, $model)
    {
        $this->type = $type;
        $this->data = $model->toArray();
    }

    public function broadcastOn(): array
    {
        // Kênh nội bộ chỉ dành cho Admin
        return [
            new PrivateChannel('admin.inventory'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'InventoryUpdatedEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}