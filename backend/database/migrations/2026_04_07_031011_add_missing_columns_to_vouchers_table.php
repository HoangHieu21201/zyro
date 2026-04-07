<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Thêm cột is_public (Mặc định là true - công khai) đặt sau end_time
            $table->boolean('is_public')->default(true)->after('end_time');
            
            // Thêm cột deleted_at cho tính năng SoftDeletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('is_public');
            $table->dropSoftDeletes();
        });
    }
};