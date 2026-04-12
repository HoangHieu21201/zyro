<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class OrderTrackingController extends Controller
{
    // Lấy dữ liệu mô phỏng bản đồ (Tích hợp Redis linh hoạt theo trạng thái)
    public function getSimulationData($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $cacheKey = "order_tracking_simulation_{$id}_{$order->status}";

            $data = Cache::remember($cacheKey, 3600, function () use ($order) {
                $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : ($order->shipping_info ?? []);
                
                $originName = $shippingInfo['origin_city'] ?? 'Thành phố Hà Nội';
                $destName   = $shippingInfo['city'] ?? 'Thành phố Hồ Chí Minh';

                $originCoords = $this->getCoordinatesForCity($originName);
                $destCoords   = $this->getCoordinatesForCity($destName);

                $progress = 0;
                $isMoving = false;
                
                switch ($order->status) {
                    case 'pending':
                    case 'confirmed':
                    case 'processing':
                        $progress = 0.05; 
                        break;
                    case 'shipping':
                        $progress = 0.5;  
                        $isMoving = true;
                        break;
                    case 'completed':
                    case 'returned':
                        $progress = 1.0;  
                        break;
                }

                return [
                    'order_code'  => $order->order_code,
                    'status'      => $order->status,
                    'origin'      => [
                        'name' => $originName,
                        'lat'  => $originCoords[0],
                        'lng'  => $originCoords[1]
                    ],
                    'destination' => [
                        'name' => $destName,
                        'lat'  => $destCoords[0],
                        'lng'  => $destCoords[1]
                    ],
                    'progress'    => $progress,
                    'is_moving'   => $isMoving
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi dữ liệu bản đồ: ' . $e->getMessage()], 500);
        }
    }

    // Helper: Map tọa độ tĩnh
    private function getCoordinatesForCity($cityName): array
    {
        $city = mb_strtolower($cityName, 'UTF-8');
        
        $coordsMap = [
            'hà nội'          => [21.0285, 105.8542],
            'hồ chí minh'     => [10.8231, 106.6297],
            'đà nẵng'         => [16.0471, 108.2068],
            'hải phòng'       => [20.8449, 106.6881],
            'cần thơ'         => [10.0452, 105.7469],
            'đắk lắk'         => [12.6667, 108.0383],
            'lâm đồng'        => [12.6667, 108.0383],
            'nghệ an'         => [18.6733, 105.6813],
            'thanh hóa'       => [19.2973, 105.2974],
            'bình dương'      => [11.5465, 108.0142],
            'đồng nai'        => [11.3503, 106.7644],
            'quảng ninh'      => [20.9599, 107.0962],
            'thừa thiên huế'   => [16.4637, 107.5909],
            'khánh hòa'       => [12.2388, 109.1967]
        ];

        foreach ($coordsMap as $key => $coords) {
            if (str_contains($city, $key)) {
                return $coords;
            }
        }

        return [16.0471, 108.2068];
    }
}