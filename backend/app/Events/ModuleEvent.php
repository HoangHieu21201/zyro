<?php

// File: backend/app/Events/ModuleEvent.php

namespace App\Events;

use App\Models\ModulePermission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModuleEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public ModulePermission $module;

    public function __construct(string $action, ModulePermission $module)
    {
        $this->action = $action;
        $this->module = $module;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.modules'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ModuleEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'module' => $this->module->toArray(),
        ];
    }
}