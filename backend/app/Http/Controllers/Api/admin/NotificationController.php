<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của Admin đang đăng nhập
     */
    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();

        // Sử dụng truy vấn trực tiếp từ DatabaseNotification để chống lỗi undefined method
        $notifications = DatabaseNotification::where('notifiable_id', $admin->id)
            ->where('notifiable_type', get_class($admin))
            ->latest()
            ->paginate(15);

        $unreadCount = DatabaseNotification::where('notifiable_id', $admin->id)
            ->where('notifiable_type', get_class($admin))
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
            'data'         => $notifications
        ]);
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $admin = $request->user();
        
        $notification = DatabaseNotification::where('notifiable_id', $admin->id)
            ->where('notifiable_type', get_class($admin))
            ->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        $unreadCount = DatabaseNotification::where('notifiable_id', $admin->id)
            ->where('notifiable_type', get_class($admin))
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đọc',
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Đánh dấu tất cả là đã đọc
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $admin = $request->user();
        
        DatabaseNotification::where('notifiable_id', $admin->id)
            ->where('notifiable_type', get_class($admin))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đọc tất cả',
            'unread_count' => 0
        ]);
    }

    /**
     * Xóa 1 thông báo
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $admin = $request->user();
        
        $notification = DatabaseNotification::where('notifiable_id', $admin->id)
            ->where('notifiable_type', get_class($admin))
            ->find($id);

        if ($notification) {
            $notification->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa thông báo'
        ]);
    }
}