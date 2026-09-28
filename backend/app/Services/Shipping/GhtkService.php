<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhtkService implements ShippingProviderInterface
{
    public function checkStatus(string $trackingCode): array
    {
        try {
            // Mock API call to GHTK
            // $response = Http::withHeaders(['Token' => config('services.ghtk.token')])
            //                 ->get("https://services.ghtk.vn/services/shipment/v2/{$trackingCode}");
            
            // For now, simulate response based on tracking code ending
            if (str_ends_with($trackingCode, 'COMPLETED')) {
                return ['status' => 'completed', 'provider_status' => '45', 'message' => 'Giao hàng thành công'];
            }
            
            if (str_ends_with($trackingCode, 'RETURNED')) {
                return ['status' => 'returned', 'provider_status' => '21', 'message' => 'Trả hàng'];
            }
            
            // Still shipping
            return ['status' => 'shipping', 'provider_status' => '4', 'message' => 'Đang giao hàng'];
            
        } catch (\Exception $e) {
            Log::error("GHTK Check Status Failed for {$trackingCode}: " . $e->getMessage());
            return ['status' => 'shipping', 'provider_status' => 'error', 'message' => 'Lỗi kết nối API'];
        }
    }
}
