<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. BẢNG PHIÊN CHAT (CONVERSATIONS)
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            
            // Liên kết người dùng (Null nếu là khách vãng lai)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id')->nullable()->index(); // Khách vãng lai
            
            // Admin đang hỗ trợ (Null nếu Bot đang tiếp)
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            
            // Trạng thái phiên chat
            $table->enum('status', ['bot_handling', 'admin_handling', 'resolved'])->default('bot_handling');
            
            // Cache tin nhắn cuối (Giúp Admin load list cực nhanh không cần JOIN)
            $table->string('last_message_snippet', 255)->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            
            $table->timestamps();

            // Index kép thần thánh giúp Admin Dashboard truy vấn mili-giây
            $table->index(['status', 'last_message_at']);
        });

        // 2. BẢNG CHI TIẾT TIN NHẮN (MESSAGES)
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            
            // Phân loại người gửi (Polymorphic-like)
            $table->enum('sender_type', ['user', 'admin', 'bot', 'system']);
            $table->unsignedBigInteger('sender_id')->nullable()->index(); // Chứa ID của user hoặc admin
            
            // Loại tin nhắn và Nội dung
            $table->string('message_type')->default('text'); // text, image, product_suggestion, order_info, system_event
            $table->longText('content')->nullable(); // Lưu text thường hoặc chuỗi JSON
            
            // Trạng thái Đọc
            $table->boolean('is_read_by_admin')->default(false);
            $table->boolean('is_read_by_user')->default(false);
            
            $table->timestamps();
        });

        // 3. BẢNG TÙY CHỈNH BOT (CHATBOT CONFIGS)
        Schema::create('chatbot_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_configs');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};