<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class AdminOrderAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $status;
    public $note;
    public $adminName;

    public function __construct(Order $order, $status, $note, $adminName)
    {
        $this->order = $order;
        $this->status = $status;
        $this->note = $note;
        $this->adminName = $adminName;
    }

    public function build()
    {
        return $this->subject("[ZYRO ALERT] Đơn hàng {$this->order->order_code} - " . strtoupper($this->status))
                    ->view('emails.admin.order_alert');
    }
}