<?php

namespace App\Events;

use App\Models\Lookbook;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LookbookEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Lookbook $lookbook;

    public function __construct(string $action, Lookbook $lookbook)
    {
        $this->action = $action;
        $this->lookbook = $lookbook;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.lookbooks'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'LookbookEvent';
    }

    public function broadcastWith(): array
    {
        if ($this->action !== 'deleted') {
            $this->lookbook->load('items');
        }

        return [
            'action'   => $this->action,
            'lookbook' => $this->lookbook->toArray(),
        ];
    }
}