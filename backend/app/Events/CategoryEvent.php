<?php

// File: backend/app/Events/CategoryEvent.php

namespace App\Events;

use App\Models\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Category $category;

    public function __construct(string $action, Category $category)
    {
        $this->action = $action;
        $this->category = $category;
    }

    public function broadcastOn(): array
    {
        // Đăng ký kênh riêng cho Categories
        return [
            new PrivateChannel('admin.categories'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CategoryEvent';
    }

    public function broadcastWith(): array
    {
        // Load kèm parent để Frontend dễ hiển thị cây danh mục (Tree View)
        if (!$this->category->relationLoaded('parent') && $this->action !== 'deleted') {
            $this->category->load('parent');
        }

        return [
            'action'   => $this->action,
            'category' => $this->category->toArray(),
        ];
    }
}