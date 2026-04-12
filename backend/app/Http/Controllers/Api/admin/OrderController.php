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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    private string $cacheVersionKey = 'orders_admin_cache_version';

    private function clearCache(): void
    {
        Cache::increment($this->cacheVersionKey);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $version = Cache::rememberForever($this->cacheVersionKey, fn() => 1);
            
            $cacheKey = sprintf('orders_admin_v%s_p%s_s%s_ps%s_df%s_dt%s_ir%s_rt%s_q%s',
                $version,
                $request->get('page', 1),
                $request->get('status', 'all'),
                $request->get('payment_status', 'all'),
                $request->get('date_from', 'all'),
                $request->get('date_to', 'all'),
                $request->boolean('is_return') ? '1' : '0',
                $request->get('return_tab', 'all'),
                Str::slug($request->get('search', ''))
            );

            $result = Cache::remember($cacheKey, 86400, function () use ($request) {
                $query = Order::with(['user:id,full_name,email,avatar_url,phone'])
                    ->withCount('items')
                    ->withTrashed()
                    ->orderBy('id', 'desc');

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

                $counts = [];

                if ($request->boolean('is_return')) {
                    $query->where(function ($q) {
                        $q->where('status', 'returned')
                            ->orWhere(function ($sub) {
                                $sub->where('status', 'cancelled')->whereIn('payment_status', ['paid', 'refunded']);
                            });
                    });

                    $countQuery = clone $query;
                    $allReturns = $countQuery->get(['payment_status', 'refunded_amount']);

                    $counts = [
                        'all'       => $allReturns->count(),
                        'pending'   => $allReturns->where('payment_status', 'paid')->whereNull('refunded_amount')->count(),
                        'proposing' => $allReturns->filter(fn($q) => $q->payment_status === 'paid' && $q->refunded_amount !== null && (float)$q->refunded_amount > 0)->count(),
                        'refunded'  => $allReturns->where('payment_status', 'refunded')->count(),
                        'rejected'  => $allReturns->filter(fn($q) => $q->payment_status === 'paid' && $q->refunded_amount !== null && (float)$q->refunded_amount === 0.0)->count(),
                    ];

                    if ($request->has('return_tab') && $request->return_tab !== 'all') {
                        $tab = $request->return_tab;
                        if ($tab === 'pending') {
                            $query->where('payment_status', 'paid')->whereNull('refunded_amount');
                        } elseif ($tab === 'proposing') {
                            $query->where('payment_status', 'paid')->whereNotNull('refunded_amount')->where('refunded_amount', '>', 0);
                        } elseif ($tab === 'refunded') {
                            $query->where('payment_status', 'refunded');
                        } elseif ($tab === 'rejected') {
                            $query->where('payment_status', 'paid')->whereNotNull('refunded_amount')->where('refunded_amount', 0);
                        }
                    }
                } else {
                    $query->where('status', '!=', 'returned');
                    $query->whereNot(function ($q) {
                        $q->where('status', 'cancelled')->whereIn('payment_status', ['paid', 'refunded']);
                    });

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

                    if ($request->has('status') && $request->status !== '') {
                        $statusArr = explode(',', $request->status);
                        $query->whereIn('status', $statusArr);
                    }
                }

                return [
                    'orders' => $query->paginate(15),
                    'counts' => $counts
                ];
            });

            return response()->json([
                'success' => true, 
                'data' => $result['orders'], 
                'counts' => $result['counts']
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

            $this->clearCache();
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
                    $noteMsg = $data['note'] ?? 'Đơn hàng của bạn đã được chuyển qua giai đoạn xử lý tiếp theo.';

                    if ($customerEmail) {
                        $subject = "Cập nhật trạng thái đơn hàng #{$order->order_code}";
                        $htmlContent = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #EBF1F5; border-radius: 10px;'>
                                <h2 style='color: #213448; text-align: center; letter-spacing: 2px;'>ZYRO.</h2>
                                <h3 style='color: #547792; text-align: center;'>Thông báo đơn hàng</h3>
                                <p style='color: #4a5568;'>Chào bạn,</p>
                                <p style='color: #4a5568;'>Đơn hàng <strong>{$order->order_code}</strong> của bạn đã được cập nhật trạng thái thành: <strong style='color: #009981; text-transform: uppercase;'>{$newStatus}</strong>.</p>
                                <p style='color: #4a5568;'>Ghi chú từ hệ thống: {$noteMsg}</p>
                                <hr style='border: none; border-top: 1px solid #EBF1F5; margin: 30px 0;' />
                                <p style='color: #a0aec0; font-size: 12px; text-align: center;'>Cảm ơn bạn đã mua sắm tại ZYRO!</p>
                            </div>
                        ";
                        Mail::html($htmlContent, function ($mail) use ($customerEmail, $subject) {
                            $mail->to($customerEmail)->subject($subject);
                        });
                    }

                    if (in_array($newStatus, ['cancelled', 'returned'])) {
                        $adminEmail = config('mail.admin_address', 'admin@zyro.vn');
                        $adminSubject = "[ZYRO ALERT] Đơn hàng {$order->order_code} - {$newStatus}";
                        $adminHtml = "
                            <div style='font-family: Arial, sans-serif; padding: 20px;'>
                                <h2 style='color: #dc3545;'>CẢNH BÁO ĐƠN HÀNG BẤT THƯỜNG</h2>
                                <p>Đơn hàng <strong>{$order->order_code}</strong> vừa bị chuyển sang trạng thái <strong style='text-transform: uppercase;'>{$newStatus}</strong>.</p>
                                <p>Người thực hiện thao tác: <strong>" . ($admin ? $admin->fullname : 'Hệ thống') . "</strong></p>
                                <p>Lý do hủy / hoàn trả: {$noteMsg}</p>
                                <p>Vui lòng kiểm tra trên hệ thống quản trị ngay lập tức để xử lý khiếu nại (nếu có) và đối soát kho hàng.</p>
                            </div>
                        ";
                        Mail::html($adminHtml, function ($mail) use ($adminEmail, $adminSubject) {
                            $mail->to($adminEmail)->subject($adminSubject);
                        });
                    }
                } catch (\Exception $e) {
                }
            }

            $this->clearCache();

            $order->load('histories.changer');
            broadcast(new OrderEvent('updated', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!', 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý trạng thái: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);

            if (!in_array($order->status, ['cancelled', 'refunded', 'returned'])) {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể đưa vào thùng rác các đơn hàng đã bị Hủy hoặc Hoàn tiền.'], 400);
            }

            $order->delete();
            $this->clearCache();
            broadcast(new OrderEvent('deleted', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã đưa đơn hàng vào thùng rác.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $order = Order::withTrashed()->findOrFail($id);
            $order->restore();

            $this->clearCache();
            broadcast(new OrderEvent('restored', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã khôi phục đơn hàng.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
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

                if ($request->action === 'refunded') {
                    $order->payment_status = 'refunded';
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

            $this->clearCache();

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
        $completeTime = clone $order->updated_at;

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
                'description' => 'Người bán đang chuẩn bị đóng gói hàng hóa.'
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
                'location' => 'Trung tâm phân loại Trung ương',
                'status' => 'shipping',
                'description' => 'Đơn hàng đã đến trạm trung chuyển.'
            ];

            if ($now->greaterThan($arriveLocalTime) || $order->status === 'completed') {
                $events[] = [
                    'time' => $arriveLocalTime->format('d/m/Y H:i'),
                    'location' => 'Bưu cục ' . $city,
                    'status' => 'shipping',
                    'description' => "Kiện hàng đã đến trung tâm giao nhận tại khu vực {$city}."
                ];
            }

            if ($now->greaterThan($deliveryTime) || $order->status === 'completed') {
                $events[] = [
                    'time' => $deliveryTime->format('d/m/Y H:i'),
                    'location' => $city,
                    'status' => 'shipping',
                    'description' => 'Shipper đang trên đường giao hàng đến bạn. Vui lòng chú ý điện thoại để nhận hàng.'
                ];
            }
        }

        if ($order->status === 'completed') {
            $events[] = [
                'time' => $completeTime->format('d/m/Y H:i'),
                'location' => $city,
                'status' => 'completed',
                'description' => 'Giao hàng thành công. Chúc bạn có trải nghiệm tuyệt vời với sản phẩm của ZYRO!'
            ];
        } elseif ($order->status === 'returned') {
            $events[] = [
                'time' => $completeTime->format('d/m/Y H:i'),
                'location' => 'Kho ' . $baseCity,
                'status' => 'returned',
                'description' => 'Giao hàng thất bại. Đơn hàng đã được hoàn trả về kho người bán.'
            ];
        } elseif ($order->status === 'cancelled') {
            $events[] = [
                'time' => clone $order->updated_at->format('d/m/Y H:i'),
                'location' => 'Hệ thống ZYRO',
                'status' => 'cancelled',
                'description' => 'Đơn hàng đã bị hủy bỏ.'
            ];
        }

        return array_reverse($events);
    }
}