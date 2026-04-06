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
        Schema::table('lookbooks', function (Blueprint $table) {
            // Thêm cột gender, độ dài 50, cho phép null, đặt ngay sau cột description cho logic
            $table->string('gender', 50)->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lookbooks', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};