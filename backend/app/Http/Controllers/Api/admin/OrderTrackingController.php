<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderTrackingController extends Controller
{
    /**
     * Lấy tọa độ giả lập cho tính năng Live Map Tracking
     */
    public function getSimulationData($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : ($order->shipping_info ?? []);
            
            $originName = $shippingInfo['origin_city'] ?? 'Thành phố Hà Nội';
            $destName = $shippingInfo['city'] ?? 'Thành phố Hồ Chí Minh';

            $originCoords = $this->getCoordinatesForCity($originName);
            $destCoords = $this->getCoordinatesForCity($destName);

            // Tính toán % tiến độ hiện tại dựa trên trạng thái
            $progress = 0;
            $isMoving = false;
            
            switch ($order->status) {
                case 'pending':
                case 'confirmed':
                case 'processing':
                    $progress = 0.05; // Mới ở kho xuất phát
                    break;
                case 'shipping':
                    $progress = 0.5;  // Đang đi giữa chừng (Sẽ animate xe chạy)
                    $isMoving = true;
                    break;
                case 'completed':
                case 'returned':
                    $progress = 1.0;  // Đã đến đích
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_code' => $order->order_code,
                    'status' => $order->status,
                    'origin' => [
                        'name' => $originName,
                        'lat' => $originCoords[0],
                        'lng' => $originCoords[1]
                    ],
                    'destination' => [
                        'name' => $destName,
                        'lat' => $destCoords[0],
                        'lng' => $destCoords[1]
                    ],
                    'progress' => $progress,
                    'is_moving' => $isMoving
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể tải dữ liệu bản đồ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Từ điển Tọa độ Trung tâm các tỉnh/thành phố (Rút gọn)
     * Tránh việc gọi API Geocoding bên thứ 3 quá nhiều gây block IP.
     */
    private function getCoordinatesForCity($cityName): array
    {
        $city = mb_strtolower($cityName, 'UTF-8');
        
        $coordsMap = [
            'hà nội' => [21.0285, 105.8542],
            'hồ chí minh' => [10.8231, 106.6297],
            'đà nẵng' => [16.0471, 108.2068],
            'hải phòng' => [20.8449, 106.6881],
            'cần thơ' => [10.0452, 105.7469],
            'đắk lắk' => [12.6667, 108.0383],
            'lâm đồng' => [12.6667, 108.0383],
            'nghệ an' => [18.6733, 105.6813],
            'thanh hóa' => [19.2973, 105.2974],
            'bình dương' => [11.5465, 108.0142],
            'đồng nai' => [11.3503, 106.7644],
            'quảng ninh' => [20.9599, 107.0962],
            'thừa thiên huế' => [16.4637, 107.5909],
            'khánh hòa' => [12.2388, 109.1967]
        ];

        // Tìm kiếm có chứa từ khóa
        foreach ($coordsMap as $key => $coords) {
            if (str_contains($city, $key)) {
                return $coords;
            }
        }

        // Nếu không tìm thấy, random một điểm ở miền Trung (Đà Nẵng) để xe vẫn có đường chạy
        return [16.0471, 108.2068];
    }
}