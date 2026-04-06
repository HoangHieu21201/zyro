<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lookbooks', function (Blueprint $table) {
            // Thêm cột deleted_at vào sau cùng
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::table('lookbooks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};