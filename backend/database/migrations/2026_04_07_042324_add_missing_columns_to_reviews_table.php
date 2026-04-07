<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Lưu cứng tên phân loại (VD: Màu Đen, Size L). Không dùng FK variant_id vì variant có thể bị shop xóa sau này.
            $table->string('variant_name', 255)->nullable()->after('product_id')->comment('Phân loại hàng đã mua');
            
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('variant_name');
            $table->dropSoftDeletes();
        });
    }
};