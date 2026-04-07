<?php

namespace App\Events;

use App\Models\Wishlist;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WishlistEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Wishlist $wishlist;

    public function __construct(string $action, Wishlist $wishlist)
    {
        $this->action = $action;
        $this->wishlist = $wishlist;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.wishlists'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'WishlistEvent';
    }

    public function broadcastWith(): array
    {
        // Đảm bảo luôn load thông tin User và Product để Frontend hiển thị realtime
        if ($this->action !== 'deleted') {
            $this->wishlist->load(['user:id,full_name,avatar_url', 'product:id,name,thumbnail_image']);
        }

        return [
            'action'   => $this->action,
            'wishlist' => $this->wishlist->toArray(),
        ];
    }
}