<?php

namespace App\Events;

use App\Models\Review;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Review $review;

    public function __construct(string $action, Review $review)
    {
        $this->action = $action;
        $this->review = $review;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.reviews'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ReviewEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'review' => $this->review->toArray(),
        ];
    }
}