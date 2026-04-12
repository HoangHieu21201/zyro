<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Order $order;

    public function __construct(string $action, Order $order)
    {
        $this->action = $action;
        $this->order = $order;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderEvent';
    }

    public function broadcastWith(): array
    {
        if (!$this->order->relationLoaded('user') && $this->action !== 'deleted') {
            $this->order->load('user:id,full_name,email,avatar_url,phone');
        }

        return [
            'action' => $this->action,
            'order'  => $this->order->toArray(),
        ];
    }
}