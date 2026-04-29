<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class ReleaseUnpaidOrders extends Command
{
    /**
     * lệnh gọi command: php artisan orders:release-unpaid
     */
    protected $signature = 'orders:release-unpaid';

    protected $description = 'Hủy các đơn hàng MoMo/VNPay chưa thanh toán quá 30 phút và nhả tồn kho.';

    public function handle()
    {
        $timeLimit = now()->subMinutes(30);

        // tìm các đơn đang pending, chưa thanh toán và qua cổng online
        $orders = Order::with('items')
            ->where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->whereIn('payment_method', ['momo', 'vnpay'])
            ->where('created_at', '<', $timeLimit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Không có đơn hàng treo nào cần xử lý.');
            return;
        }

        $count = 0;

        foreach ($orders as $order) {
            DB::transaction(function () use ($order, &$count) {
                // cập nhật trạng thái
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed'
                ]);

                // nhả hàng tạm giữ
                foreach ($order->items as $item) {
                    if ($item->variant_id) {
                        ProductVariant::where('id', $item->variant_id)->decrement('reserved_stock', $item->quantity);
                    }
                }

                // lưu log lịch sử
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'old_status' => 'pending',
                    'new_status' => 'cancelled',
                    'note' => 'Hệ thống tự động hủy đơn do quá hạn thanh toán.',
                    'changed_by_type' => 'System',
                    'changed_by' => null,
                ]);

                $count++;
            });
        }

        $this->info("Đã hủy tự động và nhả kho cho {$count} đơn hàng bị treo.");
    }
}