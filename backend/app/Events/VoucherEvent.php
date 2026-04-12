<?php

namespace App\Events;

use App\Models\Voucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Voucher $voucher;

    public function __construct(string $action, Voucher $voucher)
    {
        $this->action = $action;
        $this->voucher = $voucher;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.vouchers'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'VoucherEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'action'  => $this->action,
            'voucher' => $this->voucher->toArray(),
        ];
    }
}