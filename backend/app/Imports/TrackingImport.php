<?php

namespace App\Imports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use App\Events\OrderEvent;

class TrackingImport implements ToModel, WithHeadingRow
{
    protected $provider;
    public $successCount = 0;
    public $errorCount = 0;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
    }

    public function model(array $row)
    {
        // Flexible matching for headings (e.g. from GHTK or Zyro's own format)
        $orderCode = $row['ma_don_hang_zyro'] ?? $row['order_code'] ?? null;
        $trackingNumber = $row['ma_van_don_tracking_number'] ?? $row['tracking_number'] ?? $row['ma_van_don'] ?? null;

        if (!$orderCode || !$trackingNumber) {
            $this->errorCount++;
            return null;
        }

        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) {
            $this->errorCount++;
            return null;
        }

        // Only update if not already shipped/completed
        if (!in_array($order->status, ['shipping', 'completed', 'returned', 'cancelled'])) {
            $oldStatus = $order->status;
            
            DB::beginTransaction();
            try {
                $order->tracking_number = $trackingNumber;
                $order->shipping_provider = $this->provider;
                $order->status = 'shipping';
                $order->save();

                $order->histories()->create([
                    'user_id' => auth()->id() ?? null,
                    'action' => 'status_updated_via_excel',
                    'old_status' => $oldStatus,
                    'new_status' => 'shipping',
                    'note' => "Import mã vận đơn Excel: {$trackingNumber} ({$this->provider})"
                ]);

                DB::commit();
                $this->successCount++;
                
                // Fire event for realtime UI
                broadcast(new OrderEvent('updated', $order));

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errorCount++;
            }
        } else {
             // If already shipping, just update the tracking number
             if ($order->tracking_number !== $trackingNumber || $order->shipping_provider !== $this->provider) {
                 $order->update([
                     'tracking_number' => $trackingNumber,
                     'shipping_provider' => $this->provider
                 ]);
                 $this->successCount++;
             }
        }
        
        return null;
    }
}
