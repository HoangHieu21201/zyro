<?php

namespace App\Events;

use App\Models\Banner;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BannerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Banner $banner;

    public function __construct(string $action, Banner $banner)
    {
        $this->action = $action;
        $this->banner = $banner;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.banners'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'BannerEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'banner' => $this->banner->toArray(),
        ];
    }
}