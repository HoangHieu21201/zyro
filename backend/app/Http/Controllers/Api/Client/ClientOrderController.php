<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Admin; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Notifications\AdminAlertNotification;

class ClientOrderController extends Controller
{
    /**
     * Lấy danh sách đơn hàng của User
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            $status = $request->query('status', 'all');

            $stats = [
                'total_orders' => Order::where('user_id', $userId)->count(),
                'total_spent'  => Order::where('user_id', $userId)->where('status', 'completed')->sum('total_amount')
            ];

            $query = Order::where('user_id', $userId)
                          ->with(['items.variant.product', 'items.lookbook'])
                          ->orderBy('created_at', 'desc');

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $orders = $query->paginate(10);

            return response()->json([
                'success' => true,
                'data'    => $orders,
                'stats'   => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách đơn hàng.'], 500);
        }
    }

    /**
     * Xem chi tiết đơn hàng (BẢO VỆ BỞI IDOR)
     */
    public function show($id)
    {
        try {
            // CHỐT CHẶN IDOR: Bắt buộc order_id phải thuộc về user_id hiện tại
            $order = Order::where('user_id', Auth::id())
                ->with(['items.variant.product', 'items.lookbook', 'histories' => function($q) {
                    $q->orderBy('created_at', 'desc');
                }])
                ->findOrFail($id);

            return response()->json(['success' => true, 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem.'], 404);
        }
    }

    /**
     * Khách hàng tự hủy đơn (BẢO VỆ IDOR + LOGIC STATUS)
     */
    public function cancel(Request $request, $id)
    {
        try {
            $order = Order::with('items')->where('user_id', Auth::id())->findOrFail($id);

            // BỨC TƯỜNG LỬA CHỐNG HỦY MUỘN (GÓC KHUẤT 4)
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể hủy vì đơn hàng này đã được xác nhận hoặc đang được giao.'
                ], 400);
            }

            $reason = $request->input('reason', 'Khách hàng tự hủy đơn');

            DB::transaction(function () use ($order, $reason) {
                $order->update([
                    'status' => 'cancelled',
                    // Nếu là MoMo đã paid thì cần chờ kế toán refund, ở đây chỉ đổi trạng thái
                ]);

                // Hoàn lại Tồn kho hoặc Tồn kho tạm giữ
                foreach ($order->items as $item) {
                    if ($item->variant_id) {
                        if ($order->payment_status === 'paid') {
                            // Đã trả tiền -> Kho thực tế đã bị trừ -> Trả lại kho thực tế
                            ProductVariant::where('id', $item->variant_id)->update([
                                'stock_quantity' => DB::raw("stock_quantity + {$item->quantity}")
                            ]);
                        } else {
                            // Chưa trả tiền (COD) -> Chỉ mới giam hàng -> Trả lại kho giam
                            ProductVariant::where('id', $item->variant_id)->update([
                                'reserved_stock' => DB::raw("GREATEST(0, COALESCE(reserved_stock, 0) - {$item->quantity})")
                            ]);
                        }
                    }
                }

                // Hoàn lại lượt dùng Voucher nếu có
                if ($order->voucher_id) {
                    DB::table('vouchers')->where('id', $order->voucher_id)->decrement('usage_count');
                }

                // Ghi lịch sử
                OrderStatusHistory::create([
                    'order_id'        => $order->id,
                    'old_status'      => 'pending',
                    'new_status'      => 'cancelled',
                    'note'            => 'Lý do hủy: ' . $reason,
                    'changed_by'      => Auth::id(),
                    'changed_by_type' => get_class(Auth::user()),
                ]);
            });

            // Gửi thông báo cho Admin
            $admins = Admin::where('role_id', 1)->where('status', 'active')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AdminAlertNotification([
                    'type'    => 'danger',
                    'title'   => 'Đơn hàng bị hủy: #' . $order->order_code,
                    'message' => 'Khách hàng vừa hủy đơn hàng. Lý do: ' . $reason,
                    'url'     => '/admin/orders/' . $order->id,
                ]));
            }

            return response()->json(['success' => true, 'message' => 'Đã hủy đơn hàng thành công.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống khi hủy đơn: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Yêu cầu Trả hàng / Hoàn tiền
     */
    public function requestReturn(Request $request, $id)
    {
        try {
            $order = Order::where('user_id', Auth::id())->findOrFail($id);

            // Bức tường lửa: Chỉ cho phép yêu cầu hoàn trả nếu đơn đã hoàn thành
            if ($order->status !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể yêu cầu đổi trả đối với đơn hàng đã hoàn thành.'], 400);
            }

            // Chặn trả hàng quá 7 ngày
            if ($order->updated_at->addDays(7)->isPast()) {
                return response()->json(['success' => false, 'message' => 'Đã quá thời hạn 7 ngày kể từ lúc nhận hàng, không thể yêu cầu trả hàng.'], 400);
            }

            $reason = $request->input('reason', 'Khách hàng yêu cầu trả hàng');

            DB::transaction(function () use ($order, $reason) {
                $order->update(['status' => 'returned']);

                OrderStatusHistory::create([
                    'order_id'        => $order->id,
                    'old_status'      => 'completed',
                    'new_status'      => 'returned',
                    'note'            => 'Yêu cầu trả hàng: ' . $reason,
                    'changed_by'      => Auth::id(),
                    'changed_by_type' => get_class(Auth::user()),
                ]);
            });

            $admins = Admin::where('role_id', 1)->where('status', 'active')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AdminAlertNotification([
                    'type'    => 'warning',
                    'title'   => 'Yêu cầu trả hàng: #' . $order->order_code,
                    'message' => 'Khách hàng yêu cầu trả hàng. Lý do: ' . $reason,
                    'url'     => '/admin/orders/' . $order->id,
                ]));
            }

            return response()->json(['success' => true, 'message' => 'Đã gửi yêu cầu đổi/trả hàng đến hệ thống.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý yêu cầu: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mua lại đơn hàng cũ (Nạp item vào giỏ hàng)
     */
    public function buyAgain($id)
    {
        try {
            $order = Order::with('items.variant')->where('user_id', Auth::id())->findOrFail($id);
            $userId = Auth::id();

            $cart = Cart::firstOrCreate(
                ['user_id' => $userId],
                ['expired_at' => Carbon::now()->addDays(30)]
            );

            DB::transaction(function () use ($order, $cart) {
                foreach ($order->items as $item) {
                    if (!$item->variant_id) continue;
                    
                    $variant = ProductVariant::with('product')->find($item->variant_id);
                    // Bỏ qua nếu sản phẩm đã xóa hoặc ngừng bán
                    if (!$variant || !$variant->product || $variant->product->status !== 'published') continue;

                    $currentStock = $variant->stock_quantity - ($variant->reserved_stock ?? 0);
                    if ($currentStock <= 0) continue; // Hết hàng thì bỏ qua

                    $qtyToAdd = min($item->quantity, $currentStock, 50); // Không quá 50 chiếc

                    $existingItem = CartItem::where('cart_id', $cart->id)
                                            ->where('variant_id', $variant->id)
                                            ->where('lookbook_id', $item->lookbook_id)
                                            ->first();

                    if ($existingItem) {
                        $newQty = min($existingItem->quantity + $qtyToAdd, 50, $currentStock);
                        $existingItem->update(['quantity' => $newQty]);
                    } else {
                        CartItem::create([
                            'cart_id'             => $cart->id,
                            'product_id'          => $variant->product_id,
                            'variant_id'          => $variant->id,
                            'quantity'            => $qtyToAdd,
                            'lookbook_id'         => $item->lookbook_id,
                            'lookbook_selections' => $item->lookbook_selections
                        ]);
                    }
                }
            });

            return response()->json(['success' => true, 'message' => 'Đã thêm các sản phẩm khả dụng vào giỏ hàng.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi thao tác: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lấy dữ liệu mô phỏng vận chuyển (Dành cho Frontend Tracking)
     */
    public function getSimulationData($id)
    {
        try {
            $order = Order::where('user_id', Auth::id())->findOrFail($id);
            $events = [];

            $createdAt = Carbon::parse($order->created_at);
            $updatedAt = Carbon::parse($order->updated_at);
            $now = Carbon::now();

            $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : $order->shipping_info;
            $city = $shippingInfo['city'] ?? 'Hà Nội';
            $baseCity = $shippingInfo['origin_city'] ?? 'Hà Nội';

            // Sự kiện 1: Đặt hàng
            $events[] = [
                'time' => $createdAt->format('d/m/Y H:i'),
                'location' => 'Hệ thống ZYRO',
                'status' => 'pending',
                'description' => 'Khách hàng tạo đơn hàng thành công.'
            ];

            // Nếu đã duyệt
            if (in_array($order->status, ['processing', 'shipping', 'completed', 'returned'])) {
                $processTime = $createdAt->copy()->addHours(2);
                if ($processTime->greaterThan($updatedAt)) $processTime = $updatedAt;
                
                $events[] = [
                    'time' => $processTime->format('d/m/Y H:i'),
                    'location' => 'Kho ' . $baseCity,
                    'status' => 'processing',
                    'description' => 'Đơn hàng đã được xác nhận và đóng gói.'
                ];
            }

            // Nếu đang giao / đã hoàn thành
            if (in_array($order->status, ['shipping', 'completed', 'returned'])) {
                $shipTime = $createdAt->copy()->addHours(6);
                if ($shipTime->greaterThan($updatedAt)) $shipTime = $updatedAt;

                $events[] = [
                    'time' => $shipTime->format('d/m/Y H:i'),
                    'location' => 'Bưu cục ' . $baseCity,
                    'status' => 'shipping',
                    'description' => 'Bàn giao cho đơn vị vận chuyển.'
                ];

                $arriveLocalTime = $shipTime->copy()->addDays(1);
                $deliveryTime = $arriveLocalTime->copy()->addHours(8);
                $completeTime = $order->status === 'completed' ? clone $updatedAt : $deliveryTime->copy()->addHours(4);

                if ($now->greaterThan($arriveLocalTime) || $order->status === 'completed') {
                    $events[] = ['time' => $arriveLocalTime->format('d/m/Y H:i'), 'location' => $city, 'status' => 'shipping', 'description' => "Đến trạm {$city}."];
                }
                if ($now->greaterThan($deliveryTime) || $order->status === 'completed') {
                    $events[] = ['time' => $deliveryTime->format('d/m/Y H:i'), 'location' => $city, 'status' => 'shipping', 'description' => 'Nhân viên đang đi giao hàng.'];
                }
            }

            // Kết quả cuối cùng
            if ($order->status === 'completed') {
                $events[] = [
                    'time' => $updatedAt->format('d/m/Y H:i'),
                    'location' => $city,
                    'status' => 'completed',
                    'description' => 'Giao hàng thành công. Cảm ơn bạn đã đồng hành cùng ZYRO!'
                ];
            } elseif ($order->status === 'returned') {
                $events[] = [
                    'time' => $updatedAt->format('d/m/Y H:i'),
                    'location' => 'Kho ' . $baseCity,
                    'status' => 'returned',
                    'description' => 'Giao hàng thất bại hoặc có yêu cầu hoàn trả.'
                ];
            } elseif ($order->status === 'cancelled') {
                $events[] = [
                    'time' => $updatedAt->format('d/m/Y H:i'),
                    'location' => 'Hệ thống ZYRO',
                    'status' => 'cancelled',
                    'description' => 'Đơn hàng đã bị hủy.'
                ];
            }

            // Sắp xếp lại lịch sử theo thời gian mới nhất lên đầu để giống Shopee
            usort($events, function($a, $b) {
                return Carbon::createFromFormat('d/m/Y H:i', $b['time'])->timestamp - Carbon::createFromFormat('d/m/Y H:i', $a['time'])->timestamp;
            });

            return response()->json(['success' => true, 'data' => $events]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể tải hành trình đơn hàng.'], 500);
        }
    }
}