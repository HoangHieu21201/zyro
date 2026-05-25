<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSentEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminChatController extends Controller
{
    public function index()
    {
        try {
            $conversations = Conversation::with(['user' => function($q) {
                    // ĐÃ FIX LỖI 500: Gỡ bỏ ->select() vì nó gây xung đột SQL khi chạy chung với ->withSum()
                    $q->with('tier')
                      ->withSum(['orders as total_spent' => function($sq) {
                          $sq->where('status', 'completed');
                      }], 'total_amount');
                }])
                ->orderBy('last_message_at', 'desc')
                ->get();
                
            return response()->json(['success' => true, 'data' => $conversations]);
        } catch (\Exception $e) {
            Log::error("Lỗi AdminChatController@index: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi máy chủ khi tải danh sách chat.'], 500);
        }
    }

    public function messages($id)
    {
        try {
            $messages = Message::where('conversation_id', $id)->orderBy('created_at', 'asc')->get();
            return response()->json(['success' => true, 'data' => $messages]);
        } catch (\Exception $e) {
            Log::error("Lỗi AdminChatController@messages: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi tải chi tiết tin nhắn.'], 500);
        }
    }

    public function takeover(Request $request, $id)
    {
        try {
            $admin = $request->user();
            $conversation = Conversation::findOrFail($id);
            
            $conversation->update([
                'status' => 'admin_handling',
                'admin_id' => $admin->id
            ]);

            // Tránh lỗi lấy sai tên admin (fullname hay full_name)
            $adminName = $admin->fullname ?? $admin->full_name ?? 'nhân viên';

            $sysMsg = $conversation->messages()->create([
                'sender_type' => 'system',
                'content' => "Nhân viên {$adminName} đã tham gia cuộc trò chuyện."
            ]);
            
            broadcast(new MessageSentEvent($sysMsg))->toOthers();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Lỗi AdminChatController@takeover: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể tiếp quản phiên chat.'], 500);
        }
    }

    public function resolve(Request $request, $id)
    {
        try {
            $conversation = Conversation::findOrFail($id);
            $conversation->update(['status' => 'resolved']);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Lỗi AdminChatController@resolve: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi kết thúc phiên chat.'], 500);
        }
    }

    public function sendMessage(Request $request, $id)
    {
        try {
            $request->validate(['message' => 'required|string']);
            
            $admin = $request->user();
            $conversation = Conversation::findOrFail($id);
            
            $msg = $conversation->messages()->create([
                'sender_type' => 'admin',
                'sender_id'   => $admin->id,
                'message_type'=> 'text',
                'content'     => $request->message
            ]);

            $conversation->update([
                'last_message_snippet' => Str::limit($request->message, 50),
                'last_message_at'      => now()
            ]);

            broadcast(new MessageSentEvent($msg))->toOthers();

            return response()->json(['success' => true, 'data' => $msg]);
        } catch (\Exception $e) {
            Log::error("Lỗi AdminChatController@sendMessage: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi không thể gửi tin nhắn.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $conversation = Conversation::findOrFail($id);
            $conversation->messages()->delete();
            $conversation->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Lỗi AdminChatController@destroy: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi xóa lịch sử chat.'], 500);
        }
    }
}