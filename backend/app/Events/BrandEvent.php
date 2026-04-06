<?php

// File: backend/app/Events/BrandEvent.php

namespace App\Events;

use App\Models\Brand;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrandEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Brand $brand;

    public function __construct(string $action, Brand $brand)
    {
        $this->action = $action;
        $this->brand = $brand;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.brands'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'BrandEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'brand'  => $this->brand->toArray(),
        ];
    }
}