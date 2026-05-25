<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Events\MessageSentEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function fetchHistory(Request $request)
    {
        try {
            $userId = auth('sanctum')->id();
            $sessionId = $request->input('session_id');

            if (!$userId && !$sessionId) return response()->json(['success' => false, 'data' => []]);

            $conversation = Conversation::where('user_id', $userId)->orWhere('session_id', $sessionId)->first();

            if (!$conversation) {
                return response()->json(['success' => true, 'data' => [], 'conversation_id' => null, 'status' => 'bot_handling']);
            }

            $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();

            return response()->json([
                'success'         => true, 
                'data'            => $messages,
                'conversation_id' => $conversation->id,
                'status'          => $conversation->status
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi Fetch History Chat: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi server.']);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required_without:user_id|string',
        ]);

        $userId = auth('sanctum')->id();
        $sessionId = $request->input('session_id');
        $userMessage = $request->input('message');

        $conversation = Conversation::firstOrCreate(
            ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId],
            ['status' => 'admin_handling'] 
        );

        $userMsgRecord = $conversation->messages()->create([
            'sender_type'  => 'user',
            'sender_id'    => $userId,
            'message_type' => 'text',
            'content'      => $userMessage,
        ]);
        
        $conversation->update(['last_message_snippet' => Str::limit($userMessage, 50), 'last_message_at' => now()]);
        
        broadcast(new MessageSentEvent($userMsgRecord))->toOthers();

        return response()->json(['success' => true, 'data' => $userMsgRecord]);
    }

    public function requestHuman(Request $request)
    {
        try {
            $userId = auth('sanctum')->id();
            $sessionId = $request->input('session_id');

            $conversation = Conversation::firstOrCreate(
                ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId]
            );

            $conversation->update(['status' => 'admin_handling', 'last_message_at' => now()]);

            $sysMsg = $conversation->messages()->create([
                'sender_type'  => 'system',
                'message_type' => 'system_event',
                'content'      => 'Khách hàng yêu cầu gặp nhân viên.',
            ]);

            broadcast(new MessageSentEvent($sysMsg))->toOthers();

            return response()->json(['success' => true, 'status' => 'admin_handling']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi server.']);
        }
    }

    // XỬ LÝ API NGẮT KẾT NỐI VỚI NHÂN VIÊN -> CẬP NHẬT DATABASE VỀ BOT
    public function endHuman(Request $request)
    {
        try {
            $userId = auth('sanctum')->id();
            $sessionId = $request->input('session_id');

            $conversation = Conversation::where(function($q) use ($userId, $sessionId) {
                if ($userId) $q->where('user_id', $userId);
                else $q->where('session_id', $sessionId);
            })->first();

            if ($conversation) {
                $conversation->update(['status' => 'bot_handling', 'last_message_at' => now()]);

                $sysMsg = $conversation->messages()->create([
                    'sender_type'  => 'system',
                    'message_type' => 'system_event',
                    'content'      => 'Khách hàng đã trở lại trò chuyện với Trợ lý ảo ZYRO.',
                ]);

                broadcast(new MessageSentEvent($sysMsg))->toOthers();
            }

            return response()->json(['success' => true, 'status' => 'bot_handling']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi server.']);
        }
    }

    public function clearHistory(Request $request)
    {
        $userId = auth('sanctum')->id();
        $sessionId = $request->input('session_id');

        $query = Conversation::query();
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $conversation = $query->first();
        if ($conversation) {
            $conversation->messages()->delete();
            $conversation->delete();
        }

        return response()->json(['success' => true]);
    }
}