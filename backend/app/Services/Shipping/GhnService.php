<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhnService implements ShippingProviderInterface
{
    public function checkStatus(string $trackingCode): array
    {
        try {
            // Mock API call to GHN
            if (str_ends_with($trackingCode, 'COMPLETED')) {
                return ['status' => 'completed', 'provider_status' => 'delivered', 'message' => 'Giao thành công'];
            }
            
            if (str_ends_with($trackingCode, 'RETURNED')) {
                return ['status' => 'returned', 'provider_status' => 'returned', 'message' => 'Đã trả hàng'];
            }
            
            return ['status' => 'shipping', 'provider_status' => 'delivering', 'message' => 'Đang giao'];
            
        } catch (\Exception $e) {
            Log::error("GHN Check Status Failed for {$trackingCode}: " . $e->getMessage());
            return ['status' => 'shipping', 'provider_status' => 'error', 'message' => 'Lỗi kết nối API'];
        }
    }
}
