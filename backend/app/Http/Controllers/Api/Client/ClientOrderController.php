<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Review; // BẮT BUỘC PHẢI THÊM DÒNG NÀY
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientOrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');
        $dateFrom = $request->query('date_from', '');
        $dateTo = $request->query('date_to', '');
        $sort = $request->query('sort', 'desc');

        $stats = [
            'pending' => Order::where('user_id', $userId)->whereIn('status', ['pending', 'confirmed'])->count(),
            'shipping' => Order::where('user_id', $userId)->where('status', 'shipping')->count(),
            'completed' => Order::where('user_id', $userId)->where('status', 'completed')->count(),
        ];

        $query = Order::where('user_id', $userId)->with(['items.variant']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'ilike', "%{$search}%")
                  ->orWhereHas('items', function ($qItem) use ($search) {
                      $qItem->where('product_name', 'ilike', "%{$search}%");
                  });
            });
        }

        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($sort === 'asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(10);

        // ĐÃ THÊM: Quét các đơn hàng xem đã đánh giá chưa
        $orders->getCollection()->transform(function ($order) {
            $order->is_reviewed = Review::where('order_id', $order->id)->exists();
            return $order;
        });

        return response()->json([
            'success' => true, 
            'data' => $orders,
            'stats' => $stats
        ]);
    }

    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['items.variant', 'histories'])
            ->findOrFail($id);

        $order->simulated_tracking = $this->simulateTracking($order);
        // ĐÃ THÊM
        $order->is_reviewed = Review::where('order_id', $order->id)->exists();

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::with('items')->where('user_id', Auth::id())->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false, 
                'message' => 'Bạn không thể hủy vì đơn hàng này đã được xác nhận hoặc đang giao.'
            ], 400);
        }

        $request->validate([ 'reason' => 'required|string|max:255' ]);

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => $order->payment_status === 'unpaid' ? 'failed' : $order->payment_status
            ]);

            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    ProductVariant::where('id', $item->variant_id)->increment('stock_quantity', $item->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => 'pending',
                'new_status' => 'cancelled',
                'note' => 'Khách hàng hủy đơn: ' . $request->reason,
                'changed_by_type' => get_class(Auth::user()),
                'changed_by' => Auth::id()
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Đã hủy đơn hàng thành công.']);
    }

    public function requestReturn(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể yêu cầu hoàn trả đối với đơn hàng đã hoàn thành.'], 400);
        }

        $request->validate([ 'reason' => 'required|string|max:500' ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'old_status' => $order->status,
            'new_status' => $order->status, 
            'note' => 'KHÁCH HÀNG YÊU CẦU HOÀN TRẢ: ' . $request->reason,
            'changed_by_type' => get_class(Auth::user()),
            'changed_by' => Auth::id()
        ]);

        return response()->json(['success' => true, 'message' => 'Yêu cầu của bạn đã được gửi. Bộ phận CSKH sẽ sớm liên hệ lại.']);
    }

    public function buyAgain($id)
    {
        $order = Order::with('items.variant.product')->where('user_id', Auth::id())->findOrFail($id);

        $cart = Cart::firstOrCreate(
            ['user_id' => Auth::id()],
            ['expired_at' => now()->addDays(30)]
        );

        $addedCount = 0;
        $outOfStockCount = 0;

        foreach ($order->items as $item) {
            $variant = $item->variant;
            if (!$variant || !$variant->product || $variant->product->status !== 'published') {
                $outOfStockCount++;
                continue;
            }

            $currentStock = $variant->stock_quantity - ($variant->reserved_stock ?? 0);
            if ($currentStock < 1) {
                $outOfStockCount++;
                continue;
            }

            $addQty = min($item->quantity, $currentStock, 50);
            $existingCartItem = CartItem::where('cart_id', $cart->id)->where('variant_id', $variant->id)->first();

            if ($existingCartItem) {
                $newQty = min($existingCartItem->quantity + $addQty, $currentStock, 50);
                $existingCartItem->update(['quantity' => $newQty]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'quantity' => $addQty
                ]);
            }
            $addedCount++;
        }

        if ($addedCount === 0) {
            return response()->json(['success' => false, 'message' => 'Các sản phẩm này hiện đã hết hàng hoặc ngừng kinh doanh.'], 400);
        }

        $msg = "Đã thêm thành công $addedCount sản phẩm vào giỏ hàng.";
        if ($outOfStockCount > 0) $msg .= " (Bỏ qua $outOfStockCount SP hết hàng).";

        return response()->json(['success' => true, 'message' => $msg]);
    }

    private function simulateTracking(Order $order): array
    {
        $events = [];
        $createdAt = Carbon::parse($order->created_at);
        $now = now();

        $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : $order->shipping_info;
        $city = $shippingInfo['city'] ?? 'Hà Nội';
        $baseCity = $shippingInfo['origin_city'] ?? 'Hà Nội';
        $isSameCity = str_contains($city, $baseCity) || str_contains($city, 'Ha Noi');

        $confirmTime = $createdAt->copy()->addHours(2);
        $pickupTime = $confirmTime->copy()->addHours(8);
        $transitTime = $pickupTime->copy()->addHours(12);
        $arriveLocalTime = $transitTime->copy()->addHours($isSameCity ? 10 : 36);
        $deliveryTime = $arriveLocalTime->copy()->addHours(6);
        $completeTime = clone $order->updated_at;

        $events[] = ['time' => $createdAt->format('d/m/Y H:i'), 'location' => 'Hệ thống', 'status' => 'pending', 'description' => 'Đã đặt hàng.'];

        if (!in_array($order->status, ['pending', 'cancelled'])) {
            $events[] = ['time' => $confirmTime->format('d/m/Y H:i'), 'location' => 'Kho ' . $baseCity, 'status' => 'confirmed', 'description' => 'Đang chuẩn bị hàng.'];
        }

        if (in_array($order->status, ['shipping', 'completed', 'returned'])) {
            $events[] = ['time' => $pickupTime->format('d/m/Y H:i'), 'location' => 'Kho ' . $baseCity, 'status' => 'shipping', 'description' => 'Đã giao cho ĐVVC.'];
            if ($now->greaterThan($arriveLocalTime) || $order->status === 'completed') {
                $events[] = ['time' => $arriveLocalTime->format('d/m/Y H:i'), 'location' => $city, 'status' => 'shipping', 'description' => "Đến trạm {$city}."];
            }
            if ($now->greaterThan($deliveryTime) || $order->status === 'completed') {
                $events[] = ['time' => $deliveryTime->format('d/m/Y H:i'), 'location' => $city, 'status' => 'shipping', 'description' => 'Đang đi giao.'];
            }
        }

        if ($order->status === 'completed') {
            $events[] = ['time' => $completeTime->format('d/m/Y H:i'), 'location' => $city, 'status' => 'completed', 'description' => 'Giao thành công.'];
        } elseif ($order->status === 'returned') {
            $events[] = ['time' => $completeTime->format('d/m/Y H:i'), 'location' => 'Kho ' . $baseCity, 'status' => 'returned', 'description' => 'Hoàn trả hàng.'];
        } elseif ($order->status === 'cancelled') {
            $events[] = ['time' => Carbon::parse($order->updated_at)->format('d/m/Y H:i'), 'location' => 'Hệ thống', 'status' => 'cancelled', 'description' => 'Đã hủy đơn.'];
        }

        return array_reverse($events);
    }
}