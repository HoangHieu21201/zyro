<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ChatbotConfig;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Lấy lịch sử chat
     */
    public function fetchHistory(Request $request)
    {
        try {
            $userId = auth('sanctum')->id();
            $sessionId = $request->input('session_id');

            if (!$userId && !$sessionId) {
                return response()->json(['success' => false, 'data' => []]);
            }

            $conversation = Conversation::where('user_id', $userId)
                ->orWhere('session_id', $sessionId)
                ->first();

            if (!$conversation) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $messages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['success' => true, 'data' => $messages]);
        } catch (\Exception $e) {
            Log::error("Chatbot Fetch History Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi server khi tải lịch sử.']);
        }
    }

    /**
     * Xử lý tin nhắn chat chính
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required_without:user_id|string',
        ]);

        $userId = auth('sanctum')->id();
        $sessionId = $request->input('session_id');
        $userMessage = $request->input('message');

        // Tìm hoặc tạo cuộc hội thoại
        $conversation = Conversation::firstOrCreate(
            ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId]
        );

        // Cập nhật trạng thái
        if ($conversation->status !== 'bot_handling') {
            $conversation->update(['status' => 'bot_handling']);
        }

        // Lưu tin nhắn người dùng
        $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_id' => $userId,
            'message_type' => 'text',
            'content' => $userMessage,
        ]);

        $conversation->update([
            'last_message_snippet' => Str::limit($userMessage, 50),
            'last_message_at' => now()
        ]);

        // Chuẩn bị lịch sử cho AI
        $recentMessages = $conversation->messages()
            ->whereIn('sender_type', ['user', 'bot'])
            ->orderBy('created_at', 'desc')
            ->limit(11) // Lấy số lẻ để dễ xử lý xen kẽ
            ->get()
            ->reverse();

        $chatHistory = [];
        foreach ($recentMessages as $msg) {
            $role = ($msg->sender_type === 'user') ? 'user' : 'model';
            
            // Quy tắc Gemini: Không được có 2 role giống nhau liên tiếp
            if (!empty($chatHistory) && end($chatHistory)['role'] === $role) {
                $chatHistory[count($chatHistory) - 1]['parts'][0]['text'] .= "\n" . $msg->content;
            } else {
                $chatHistory[] = [
                    'role' => $role,
                    'parts' => [['text' => (string)$msg->content]]
                ];
            }
        }

        // Đảm bảo tin nhắn cuối cùng luôn là của user
        if (!empty($chatHistory) && end($chatHistory)['role'] !== 'user') {
            $chatHistory[] = ['role' => 'user', 'parts' => [['text' => (string)$userMessage]]];
        }

        // Gọi AI
        $botReply = $this->callGeminiAI($chatHistory, $userId);

        // Lưu tin nhắn Bot
        $botMsgRecord = $conversation->messages()->create([
            'sender_type' => 'bot',
            'sender_id' => null,
            'message_type' => 'text',
            'content' => $botReply,
        ]);

        $conversation->update([
            'last_message_snippet' => Str::limit($botReply, 50),
            'last_message_at' => now()
        ]);

        return response()->json(['success' => true, 'data' => $botMsgRecord]);
    }

    /**
     * Gọi API Gemini với cơ chế xoay vòng Key và RAG
     */
    private function callGeminiAI(array $chatHistory, $userId)
    {
        // 1. Lấy và làm sạch sâu API Keys (Loại bỏ khoảng trắng, dấu \n \r nếu copy lỗi)
        $keysRaw = env('GEMINI_API_KEYS', '');
        $keys = array_values(array_filter(explode(',', str_replace(["\r", "\n", "\t", '"', ' '], '', $keysRaw))));
        
        if (empty($keys)) {
            Log::error("Gemini Error: Missing API Keys in .env");
            return 'Hệ thống AI chưa được cấu hình API Key. Hãy kiểm tra file .env!';
        }

        shuffle($keys); 

        // 2. Xây dựng System Prompt (RAG)
        $basePrompt = ChatbotConfig::getValue('system_prompt', 'Bạn là ZYRO Bot, trợ lý ảo thân thiện của thương hiệu thời trang ZYRO.');
        
        $ragContext = "\n\n--- DỮ LIỆU CẬP NHẬT TỪ HỆ THỐNG ---\n";
        
        // Lấy sản phẩm nổi bật
        $topProducts = Product::where('status', 'published')->latest()->limit(5)->get();
        if ($topProducts->isNotEmpty()) {
            $ragContext .= "Sản phẩm mới nhất:\n";
            foreach ($topProducts as $p) {
                $ragContext .= "- {$p->name} | Giá: " . number_format($p->base_price) . "đ | Xem tại: /product/{$p->slug}\n";
            }
        }

        // Lấy thông tin khách hàng nếu đã login
        if ($userId) {
            $orders = Order::where('user_id', $userId)->latest()->limit(3)->get();
            if ($orders->isNotEmpty()) {
                $ragContext .= "\nLịch sử đơn hàng của khách:\n";
                foreach ($orders as $o) {
                    $ragContext .= "- Mã {$o->order_code}: {$o->status} (" . number_format($o->total_amount) . "đ)\n";
                }
            } else {
                $ragContext .= "\nKhách hàng này chưa có đơn hàng nào.\n";
            }
        }

        $systemPrompt = $basePrompt . $ragContext . "\nLưu ý: Luôn trả lời ngắn gọn, sử dụng emoji thân thiện.";

        // 3. Thực hiện gọi API
        $model = 'gemini-1.5-flash';
        $lastError = ""; // Lưu lại lỗi mạng cuối cùng để in ra chat UI

        foreach ($keys as $apiKey) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]]
                ],
                'contents' => $chatHistory,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ];
            
            try {
                // Thử kết nối trong 20s
                $response = Http::withoutVerifying()
                    ->timeout(20)
                    ->post($url, $payload);

                if ($response->successful()) {
                    $result = $response->json();
                    
                    $candidate = $result['candidates'][0] ?? null;
                    if ($candidate && isset($candidate['content']['parts'][0]['text'])) {
                        return $candidate['content']['parts'][0]['text'];
                    }

                    if (isset($candidate['finishReason']) && $candidate['finishReason'] === 'SAFETY') {
                        return "Xin lỗi, câu hỏi của bạn có nội dung mà tôi không thể phản hồi. Bạn hãy thử hỏi cách khác nhé!";
                    }
                    
                    return "Lỗi trả về từ Google: " . $response->body();
                }
                
                // Nếu Google trả về lỗi HTTP 4xx, 5xx (như sai API Key, API Key hết hạn)
                $lastError = "HTTP Error " . $response->status() . ": " . $response->body();
                Log::error("Gemini API Error: " . $lastError);
                
            } catch (\Exception $e) {
                // Nếu máy tính chặn request (cURL error, timeout, firewall...)
                $lastError = "Lỗi Mạng (cURL Exception): " . $e->getMessage();
                Log::error('Lỗi kết nối Gemini: ' . $lastError);
                continue; 
            }
        }
        
        // Trả thẳng lỗi ra màn hình chat để debug dễ dàng hơn
        return "⚠️ DEBUG MODE: Mình không thể kết nối tới Google API. Chi tiết lỗi hệ thống như sau: " . $lastError;
    }
}