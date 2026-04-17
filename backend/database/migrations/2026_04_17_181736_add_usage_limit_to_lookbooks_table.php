<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('lookbooks', function (Blueprint $table) {
            $table->integer('usage_limit')->nullable()->after('status');
            // Lệnh after() trong Laravel Migration sẽ chạy bình thường trên MySQL, 
            // và tự động bị phớt lờ (bỏ qua mà không báo lỗi) trên PostgreSQL!
        });
    }

    public function down()
    {
        Schema::table('lookbooks', function (Blueprint $table) {
            $table->dropColumn('usage_limit');
        });
    }
};
