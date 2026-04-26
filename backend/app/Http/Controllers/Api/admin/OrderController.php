<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Events\OrderEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::with(['user:id,full_name,email,avatar_url,phone', 'items:id,order_id,lookbook_id,quantity'])
                ->withCount('items')
                ->withTrashed()
                ->orderBy('id', 'desc');

            $counts = [];

            if ($request->boolean('is_return')) {
                // =========================================================
                // LOGIC SIÊU SẠCH CHO RMA: CHỈ CẦN CHECK return_status
                // =========================================================
                $query->whereNotNull('return_status');

                $countQuery = clone $query;
                $allReturns = $countQuery->get(['return_status']);

                $counts = [
                    'all'       => $allReturns->count(),
                    'pending'   => $allReturns->where('return_status', 'pending')->count(),
                    'proposing' => $allReturns->where('return_status', 'proposing')->count(),
                    'refunded'  => $allReturns->where('return_status', 'approved')->count(),
                    'rejected'  => $allReturns->where('return_status', 'rejected')->count(),
                ];

                if ($request->has('return_tab') && $request->return_tab !== 'all') {
                    $tab = $request->return_tab;
                    if ($tab === 'refunded') $tab = 'approved'; // Map Vue tab sang DB status
                    $query->where('return_status', $tab);
                }
            } else {
                // =========================================================
                // LOGIC ĐƠN HÀNG THƯỜNG: BỎ QUA MỌI ĐƠN CÓ return_status
                // =========================================================
                $query->whereNull('return_status');

                $countQuery = clone $query;
                $rawCounts = $countQuery->reorder()
                    ->select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();

                $counts = [
                    'all'        => array_sum($rawCounts),
                    'pending'    => $rawCounts['pending'] ?? 0,
                    'confirmed'  => ($rawCounts['confirmed'] ?? 0) + ($rawCounts['processing'] ?? 0),
                    'shipping'   => $rawCounts['shipping'] ?? 0,
                    'completed'  => $rawCounts['completed'] ?? 0,
                    'cancelled'  => $rawCounts['cancelled'] ?? 0,
                ];

                if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
                    $statusArr = explode(',', $request->status);
                    $query->whereIn('status', $statusArr);
                }
            }

            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_code', 'ILIKE', "%{$search}%")
                        ->orWhereRaw("shipping_info->>'phone' ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("shipping_info->>'name' ILIKE ?", ["%{$search}%"]);
                });
            }

            if ($request->has('date_from') && $request->date_from !== '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to !== '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('payment_status') && $request->payment_status !== '') {
                $query->where('payment_status', $request->payment_status);
            }

            $orders = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách đơn hàng: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $order = Order::withTrashed()
                ->with([
                    'user:id,full_name,email,avatar_url,phone',
                    'voucher',
                    'items.product:id,name,slug,thumbnail_image',
                    'items.variant:id,sku,stock_quantity',
                    'items.lookbook',
                    'histories.changer'
                ])
                ->findOrFail($id);

            $order->simulated_tracking = $this->simulateTracking($order);

            return response()->json(['success' => true, 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.'], 404);
        }
    }

    public function update(UpdateOrderRequest $request, $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $data = $request->validated();

            $shippingInfoInput = $request->input('shipping_info');

            if (!empty($shippingInfoInput) && is_array($shippingInfoInput)) {
                $currentShipping = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : ($order->shipping_info ?? []);
                $order->shipping_info = array_merge($currentShipping, $shippingInfoInput);
            }

            if (array_key_exists('shipping_provider', $data) || $request->has('shipping_provider')) {
                $order->shipping_provider = $request->input('shipping_provider', $order->shipping_provider);
            }

            if (array_key_exists('tracking_number', $data) || $request->has('tracking_number')) {
                $order->tracking_number = $request->input('tracking_number', $order->tracking_number);
            }

            if (array_key_exists('order_note', $data) || $request->has('order_note')) {
                $order->order_note = $request->input('order_note', $order->order_note);
            }

            $order->save();

            broadcast(new OrderEvent('updated', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật thông tin đơn hàng thành công!', 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(UpdateOrderStatusRequest $request, $id): JsonResponse
    {
        try {
            $order = Order::with(['user', 'items'])->findOrFail($id);
            $data = $request->validated();

            /** @var \App\Models\Admin $admin */
            $admin = $request->user();

            $oldStatus = $order->status;
            $newStatus = $data['status'] ?? $oldStatus;
            $newPaymentStatus = $data['payment_status'] ?? $order->payment_status;

            if ($oldStatus !== $newStatus) {
                $validTransitions = [
                    'pending'    => ['confirmed', 'cancelled'],
                    'confirmed'  => ['processing', 'shipping', 'cancelled'],
                    'processing' => ['shipping', 'cancelled'],
                    'shipping'   => ['completed', 'returned'],
                    'completed'  => ['returned'],
                    'cancelled'  => [],
                    'returned'   => [],
                    'refunded'   => []
                ];

                if (!in_array($newStatus, $validTransitions[$oldStatus] ?? [])) {
                    return response()->json(['success' => false, 'message' => "Lỗi Logic: Không thể chuyển từ '{$oldStatus}' sang '{$newStatus}'."], 400);
                }
            }

            DB::transaction(function () use ($order, $data, $admin, $oldStatus, $newStatus, $newPaymentStatus) {
                $hasChanged = false;

                if ($oldStatus !== $newStatus) {
                    $order->status = $newStatus;

                    // ADMIN CHỦ ĐỘNG TRẢ HÀNG => TỰ ĐỘNG BẬT CỜ RETURN_STATUS
                    if ($newStatus === 'returned' && $order->return_status === null) {
                        $order->return_status = 'pending';
                    }

                    $hasChanged = true;

                    $order->histories()->create([
                        'old_status'      => $oldStatus,
                        'new_status'      => $newStatus,
                        'note'            => $data['note'] ?? 'Cập nhật trạng thái đơn hàng',
                        'changed_by_type' => get_class($admin),
                        'changed_by'      => $admin->id,
                    ]);

                    if (in_array($newStatus, ['cancelled', 'returned'])) {
                        foreach ($order->items as $item) {
                            if ($item->variant_id) {
                                ProductVariant::where('id', $item->variant_id)->increment('stock_quantity', $item->quantity);
                            }
                        }
                    }
                }

                if ($order->payment_status !== $newPaymentStatus) {
                    $order->payment_status = $newPaymentStatus;
                    $hasChanged = true;
                }

                if (isset($data['shipping_status']) && $order->shipping_status !== $data['shipping_status']) {
                    $order->shipping_status = $data['shipping_status'];
                    $hasChanged = true;
                }

                if ($hasChanged) {
                    $order->save();
                }
            });

            if ($oldStatus !== $newStatus) {
                try {
                    $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : $order->shipping_info;
                    $customerEmail = $order->user ? $order->user->email : ($shippingInfo['email'] ?? null);

                    if ($customerEmail) {
                        if ($newStatus === 'confirmed') {
                            Mail::to($customerEmail)->queue(new \App\Mail\OrderConfirmedMail($order));
                        } elseif ($newStatus === 'completed') {
                            Mail::to($customerEmail)->queue(new \App\Mail\OrderCompletedMail($order));
                        }
                    }

                    if (in_array($newStatus, ['cancelled', 'returned'])) {
                        $adminEmail = config('mail.admin_address', 'admin@zyro.vn');
                        $noteMsg = $data['note'] ?? 'Không có ghi chú';
                        $adminName = $admin ? $admin->fullname : 'Hệ thống';

                        Mail::to($adminEmail)->queue(new \App\Mail\AdminOrderAlertMail($order, $newStatus, $noteMsg, $adminName));
                    }
                } catch (\Exception $e) {
                    Log::error('Lỗi đẩy Email vào Queue: ' . $e->getMessage());
                }
            }

            $order->load('histories.changer');
            broadcast(new OrderEvent('updated', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!', 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý trạng thái: ' . $e->getMessage()], 500);
        }
    }

    public function processRefund(Request $request, $id): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:propose,reject,refunded',
            'refund_amount' => 'required|numeric|min:0',
            'refund_note' => 'nullable|string'
        ]);

        try {
            $order = Order::findOrFail($id);
            /** @var \App\Models\Admin $admin */
            $admin = $request->user();

            DB::transaction(function () use ($order, $request, $admin) {
                $order->refunded_amount = $request->action === 'reject' ? 0 : $request->refund_amount;

                // CHỐT LUỒNG VỚI CỘT MỚI
                if ($request->action === 'refunded') {
                    $order->payment_status = 'refunded';
                    $order->return_status = 'approved';
                    if ($order->status !== 'returned') {
                        $order->status = 'returned';
                    }

                    $order->histories()->create([
                        'old_status'      => $order->status,
                        'new_status'      => $order->status,
                        'note'            => 'Kế toán xác nhận Đã chuyển khoản hoàn tiền. ' . ($request->refund_note ? 'Ghi chú: ' . $request->refund_note : ''),
                        'changed_by_type' => get_class($admin),
                        'changed_by'      => $admin->id
                    ]);
                } else {
                    $order->return_status = $request->action === 'reject' ? 'rejected' : 'proposing';

                    $historyNote = $request->action === 'propose' ? "Đã đề xuất số tiền hoàn lại: " . number_format($request->refund_amount) . "đ." : 'Đã từ chối hoàn tiền.';
                    if ($request->refund_note) $historyNote .= " | Lý do: " . $request->refund_note;

                    $order->histories()->create([
                        'old_status'      => $order->status,
                        'new_status'      => $order->status,
                        'note'            => $historyNote,
                        'changed_by_type' => get_class($admin),
                        'changed_by'      => $admin->id
                    ]);
                }
                $order->save();
            });

            $order->load('histories.changer');
            broadcast(new OrderEvent('updated', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Xử lý yêu cầu hoàn tiền thành công!', 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý hoàn tiền: ' . $e->getMessage()], 500);
        }
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
        $completeTime = \Illuminate\Support\Carbon::parse($order->updated_at ?? now());

        $events[] = [
            'time' => $createdAt->format('d/m/Y H:i'),
            'location' => 'Hệ thống ZYRO',
            'status' => 'pending',
            'description' => 'Đơn hàng được tạo thành công. Đang chờ hệ thống xác nhận.'
        ];

        if (!in_array($order->status, ['pending', 'cancelled'])) {
            $events[] = [
                'time' => $confirmTime->format('d/m/Y H:i'),
                'location' => 'Kho ' . $baseCity,
                'status' => 'confirmed',
                'description' => 'Kho ZYRO đang chuẩn bị và đóng gói trang phục.'
            ];
        }

        if (in_array($order->status, ['shipping', 'completed', 'returned'])) {
            $events[] = [
                'time' => $pickupTime->format('d/m/Y H:i'),
                'location' => 'Bưu cục kho ' . $baseCity,
                'status' => 'shipping',
                'description' => 'Đơn vị vận chuyển đã lấy hàng thành công.'
            ];

            $events[] = [
                'time' => $transitTime->format('d/m/Y H:i'),
                'location' => 'Trung tâm phân loại',
                'status' => 'shipping',
                'description' => 'Kiện hàng đang được trung chuyển.'
            ];

            if ($now->greaterThan($arriveLocalTime) || $order->status === 'completed') {
                $events[] = [
                    'time' => $arriveLocalTime->format('d/m/Y H:i'),
                    'location' => 'Bưu cục ' . $city,
                    'status' => 'shipping',
                    'description' => "Kiện hàng đã đến bưu cục giao nhận tại {$city}."
                ];
            }

            if ($now->greaterThan($deliveryTime) || $order->status === 'completed') {
                $events[] = [
                    'time' => $deliveryTime->format('d/m/Y H:i'),
                    'location' => $city,
                    'status' => 'shipping',
                    'description' => 'Shipper đang trên đường giao hàng đến bạn. Vui lòng chú ý điện thoại.'
                ];
            }
        }

        if ($order->status === 'completed') {
            $events[] = [
                'time' => $completeTime->format('d/m/Y H:i'),
                'location' => $city,
                'status' => 'completed',
                'description' => 'Giao hàng thành công. Chúc bạn có trải nghiệm thời trang tuyệt vời với ZYRO!'
            ];
        } elseif ($order->status === 'returned') {
            $events[] = [
                'time' => $completeTime->format('d/m/Y H:i'),
                'location' => 'Kho ' . $baseCity,
                'status' => 'returned',
                'description' => 'Giao hàng thất bại. Đơn hàng đã được hoàn trả về kho ZYRO.'
            ];
        } elseif ($order->status === 'cancelled') {
            $events[] = [
                'time' => \Illuminate\Support\Carbon::parse($order->updated_at ?? now())->format('d/m/Y H:i'),
                'location' => 'Hệ thống ZYRO',
                'status' => 'cancelled',
                'description' => 'Đơn hàng đã bị hủy bỏ.'
            ];
        }

        return array_reverse($events);
    }
}
