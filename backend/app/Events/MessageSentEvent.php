<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation_id;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->conversation_id = $message->conversation_id;
    }

    /**
     * Kênh phát sóng (Trùng với tên kênh trong file channels.php và Vue.js)
     */
    public function broadcastOn(): array
    {
        return [
            // Kênh public bảo mật bằng UUID (Dành cho Guest & User)
            new Channel('chat.conversation.' . $this->conversation_id),
            // Kênh tổng báo cho Admin biết có tin nhắn mới
            new Channel('admin.chats')
        ];
    }

    /**
     * Dữ liệu gửi qua Websocket
     */
    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_type'     => $this->message->sender_type,
            'message_type'    => $this->message->message_type,
            'content'         => $this->message->content,
            'created_at'      => $this->message->created_at->toISOString(),
        ];
    }

    /**
     * Bắt buộc phải có hàm này để Vue.js Echo nhận diện đúng tên sự kiện
     */
    public function broadcastAs(): string
    {
        return 'MessageSentEvent';
    }
}