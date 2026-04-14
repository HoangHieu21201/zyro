<?php

namespace App\Events;

use App\Models\FlashSale;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FlashSaleEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public FlashSale $flashSale;

    public function __construct(string $action, FlashSale $flashSale)
    {
        $this->action = $action;
        $this->flashSale = $flashSale;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.flash_sales'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'FlashSaleEvent';
    }

    public function broadcastWith(): array
    {
        if ($this->action !== 'deleted') {
            $this->flashSale->load(['items.variant.product']);
        }

        return [
            'action'     => $this->action,
            'flash_sale' => $this->flashSale->toArray(),
        ];
    }
}