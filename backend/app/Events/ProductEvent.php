<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Product $product;

    public function __construct(string $action, Product $product)
    {
        $this->action = $action;
        $this->product = $product;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.products'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ProductEvent';
    }

    public function broadcastWith(): array
    {
        if ($this->action !== 'deleted') {
            $this->product->load(['category:id,name', 'brand:id,name']);
        }

        return [
            'action'  => $this->action,
            'product' => $this->product->toArray(),
        ];
    }
}