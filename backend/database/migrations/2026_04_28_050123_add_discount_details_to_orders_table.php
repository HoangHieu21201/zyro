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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('flash_sale_discount', 10, 2)->default(0)->after('sub_total');
            $table->decimal('tier_discount', 10, 2)->default(0)->after('flash_sale_discount');
            $table->decimal('voucher_discount', 10, 2)->default(0)->after('tier_discount');
            $table->json('discount_details')->nullable()->after('voucher_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'flash_sale_discount',
                'tier_discount',
                'voucher_discount',
                'discount_details',
            ]);
        });
    }
};