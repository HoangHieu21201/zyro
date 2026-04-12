<?php

namespace App\Events;

use App\Models\Role;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Role $role;

    public function __construct(string $action, Role $role)
    {
        $this->action = $action;
        $this->role = $role;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.roles'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'RoleEvent';
    }

    public function broadcastWith(): array
    {
        if (!$this->role->relationLoaded('admins_count') && $this->action !== 'deleted') {
            $this->role->loadCount('admins');
        }

        return [
            'action' => $this->action,
            'role'   => $this->role->toArray(),
        ];
    }
}