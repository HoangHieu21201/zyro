<?php

namespace App\Events;

use App\Models\MembershipTier;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MembershipTierEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public MembershipTier $tier;

    public function __construct(string $action, MembershipTier $tier)
    {
        $this->action = $action;
        $this->tier = $tier;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.tiers'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MembershipTierEvent';
    }

    public function broadcastWith(): array
    {
        if (!$this->tier->relationLoaded('users_count') && $this->action !== 'deleted') {
            $this->tier->loadCount('users');
        }

        return [
            'action' => $this->action,
            'tier'   => $this->tier->toArray(),
        ];
    }
}