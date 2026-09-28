<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\Shipping\GhtkService;
use App\Services\Shipping\GhnService;
use App\Events\OrderEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncShippingStatus extends Command
{
    protected $signature = 'zyro:sync-shipping';
    protected $description = 'Sync order status with shipping providers (API Polling)';

    public function handle()
    {
        $this->info('Bắt đầu đồng bộ trạng thái vận chuyển...');
        
        // Fetch orders shipping within last 30 days (avoid zombie loop)
        $orders = Order::where('status', 'shipping')
            ->whereNotNull('shipping_provider')
            ->whereNotNull('tracking_number')
            ->where('updated_at', '>=', Carbon::now()->subDays(30))
            ->get();
            
        if ($orders->isEmpty()) {
            $this->info('Không có đơn hàng nào cần đồng bộ.');
            return;
        }

        $ghtkService = new GhtkService();
        $ghnService = new GhnService();

        $successCount = 0;

        foreach ($orders as $order) {
            try {
                $service = match(strtolower($order->shipping_provider)) {
                    'ghtk' => $ghtkService,
                    'ghn' => $ghnService,
                    default => null
                };

                if (!$service) {
                    $this->warn("Không tìm thấy service hỗ trợ cho: {$order->shipping_provider}");
                    continue;
                }

                $result = $service->checkStatus($order->tracking_number);
                
                // If status from provider matches Zyro's current status, skip
                if ($result['status'] === $order->status) {
                    continue;
                }
                
                // Avoid backward transitions!
                if ($order->status === 'completed' || $order->status === 'returned') {
                    continue;
                }

                // Process update
                DB::beginTransaction();
                try {
                    $oldStatus = $order->status;
                    $order->status = $result['status'];
                    $order->shipping_status = $result['provider_status'];
                    $order->save();

                    // Log history
                    $order->histories()->create([
                        'user_id' => null, // System
                        'action' => 'status_updated_by_api',
                        'old_status' => $oldStatus,
                        'new_status' => $order->status,
                        'note' => 'Hệ thống tự động cập nhật từ ĐVVC: ' . ($result['message'] ?? 'Thành công')
                    ]);

                    DB::commit();
                    $successCount++;
                    
                    // Trigger real-time update
                    broadcast(new OrderEvent('updated', $order));
                    $this->info("Đã cập nhật đơn #{$order->order_code} -> {$order->status}");
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Failed to update order {$order->order_code}: " . $e->getMessage());
                    $this->error("Lỗi cập nhật DB đơn #{$order->order_code}");
                }

                // Sleep to avoid rate limiting
                usleep(500000); // 0.5s

            } catch (\Exception $e) {
                Log::error("Cron Job Error on Order {$order->id}: " . $e->getMessage());
                $this->error("Lỗi mạng khi xử lý đơn #{$order->order_code}");
            }
        }

        $this->info("Hoàn tất! Đã đồng bộ thành công {$successCount} đơn hàng.");
    }
}
