<?php

// File: backend/app/Events/UserEvent.php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public User $user;

    public function __construct(string $action, User $user)
    {
        $this->action = $action;
        $this->user = $user;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserEvent';
    }

    public function broadcastWith(): array
    {
        // Load kèm tier để UI hiển thị hạng thành viên
        if (!$this->user->relationLoaded('tier') && $this->action !== 'deleted') {
            $this->user->load('tier');
        }

        return [
            'action' => $this->action,
            'user'   => $this->user->toArray(),
        ];
    }
}