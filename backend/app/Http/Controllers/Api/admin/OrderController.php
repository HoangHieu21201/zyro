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

class OrderController extends Controller
{
    /**
     * Lấy danh sách Đơn hàng (Có lọc siêu cấp)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::with(['user:id,full_name,email,avatar_url,phone'])
                ->withCount('items')
                ->withTrashed()
                ->orderBy('id', 'desc');

            // Bộ lọc tìm kiếm (Mã đơn, SĐT khách)
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_code', 'ILIKE', "%{$search}%")
                        ->orWhereRaw("shipping_info->>'phone' ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("shipping_info->>'name' ILIKE ?", ["%{$search}%"]);
                });
            }

            // ĐƯA CÁC BỘ LỌC CHUNG LÊN ĐẦU ĐỂ CON SỐ COUNT ĐƯỢC TÍNH TOÁN ĐÚNG THEO BỘ LỌC
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

            // PHÂN LUỒNG: ĐƠN HOÀN TRẢ VS ĐƠN BÌNH THƯỜNG
            if ($request->boolean('is_return')) {
                $query->where(function ($q) {
                    $q->where('status', 'returned')
                        ->orWhere(function ($sub) {
                            $sub->where('status', 'cancelled')->whereIn('payment_status', ['paid', 'refunded']);
                        });
                });

                // TÍNH TOÁN COUNT CHO CÁC TAB ĐƠN HOÀN TRẢ
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
                // Đơn hàng bình thường (Loại bỏ các ca hoàn trả khỏi danh sách hiển thị)
                $query->where('status', '!=', 'returned');
                $query->whereNot(function ($q) {
                    $q->where('status', 'cancelled')->whereIn('payment_status', ['paid', 'refunded']);
                });

                // TÍNH TOÁN COUNT CHO CÁC TAB ĐƠN HÀNG
                $countQuery = clone $query;
                // FIX LỖI POSTGRESQL GROUP BY: Sử dụng reorder() để xóa orderBy cũ trước khi Group
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

                // Lọc theo trạng thái
                if ($request->has('status') && $request->status !== '') {
                    $statusArr = explode(',', $request->status);
                    $query->whereIn('status', $statusArr);
                }
            }

            $orders = $query->paginate(15);

            return response()->json(['success' => true, 'data' => $orders, 'counts' => $counts]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách đơn hàng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xem chi tiết Đơn hàng & Tích hợp Tracking Mô phỏng
     */
    public function show($id): JsonResponse
    {
        try {
            $order = Order::withTrashed()
                ->with([
                    'user:id,full_name,email,avatar_url,phone',
                    'voucher',
                    'items.product:id,name,slug,thumbnail_image', // Lấy thông tin SP gốc để đối chiếu
                    'items.variant:id,sku,stock_quantity',
                    'histories.changer' // Load người đã thay đổi trạng thái
                ])
                ->findOrFail($id);

            // Gắn thông tin mô phỏng lộ trình vận chuyển vào response
            $order->simulated_tracking = $this->simulateTracking($order);

            return response()->json(['success' => true, 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.'], 404);
        }
    }

    /**
     * Cập nhật thông tin giao hàng & Ghi chú
     */
    public function update(UpdateOrderRequest $request, $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $data = $request->validated();

            $shippingInfoInput = $request->input('shipping_info');

            if (!empty($shippingInfoInput) && is_array($shippingInfoInput)) {
                // Lấy mảng info cũ từ DB (nếu có)
                $currentShipping = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : ($order->shipping_info ?? []);

                // Trộn đè dữ liệu mới (origin_city) vào mảng cũ, giữ nguyên tên, SĐT...
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

    /**
     * Cập nhật Trạng thái đơn hàng (State Machine + Gửi Email Tự động)
     */
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

            // =======================================================
            // 1. NGHIÊM NGẶT: Kiểm tra luồng trạng thái đơn hàng (State Machine)
            // =======================================================
            if ($oldStatus !== $newStatus) {
                // Định nghĩa luồng cho phép đi (Không cho phép đi lùi hoặc đi tắt)
                $validTransitions = [
                    'pending'    => ['confirmed', 'cancelled'],
                    'confirmed'  => ['processing', 'shipping', 'cancelled'],
                    'processing' => ['shipping', 'cancelled'],
                    'shipping'   => ['completed', 'returned'],
                    'completed'  => ['returned'],
                    'cancelled'  => [], // Hủy rồi thì không được phép quay xe
                    'returned'   => [], // Đã hoàn trả thì chốt sổ
                    'refunded'   => []
                ];

                if (!in_array($newStatus, $validTransitions[$oldStatus] ?? [])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Lỗi Logic: Hệ thống từ chối chuyển ngược trạng thái hoặc đi tắt từ '{$oldStatus}' sang '{$newStatus}'."
                    ], 400);
                }
            }

            // =======================================================
            // 2. CẬP NHẬT DB VÀ TRẢ LẠI KHO (Nếu Hủy/Hoàn trả)
            // =======================================================
            DB::transaction(function () use ($order, $data, $admin, $oldStatus, $newStatus, $newPaymentStatus) {
                $hasChanged = false;

                if ($oldStatus !== $newStatus) {
                    $order->status = $newStatus;
                    $hasChanged = true;

                    // Ghi log vào bảng order_status_histories
                    $order->histories()->create([
                        'old_status'      => $oldStatus,
                        'new_status'      => $newStatus,
                        'note'            => $data['note'] ?? 'Cập nhật trạng thái đơn hàng',
                        'changed_by_type' => get_class($admin),
                        'changed_by'      => $admin->id,
                    ]);

                    // Hoàn lại kho thực tế nếu đơn bị Hủy hoặc Hoàn trả
                    if (in_array($newStatus, ['cancelled', 'returned'])) {
                        foreach ($order->items as $item) {
                            if ($item->variant_id) {
                                ProductVariant::where('id', $item->variant_id)->increment('stock_quantity', $item->quantity);
                            }
                        }
                    }
                }

                // Cập nhật các trạng thái thanh toán/giao hàng
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

            // =======================================================
            // 3. GỬI EMAIL THÔNG BÁO TỰ ĐỘNG
            // =======================================================
            if ($oldStatus !== $newStatus) {
                try {
                    $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : $order->shipping_info;
                    $customerEmail = $order->user ? $order->user->email : ($shippingInfo['email'] ?? null);
                    $noteMsg = $data['note'] ?? 'Đơn hàng của bạn đã được chuyển qua giai đoạn xử lý tiếp theo.';

                    // a) Gửi email cho Khách hàng
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

                    // b) Gửi email Cảnh báo bảo mật cho Quản trị viên chỉ định khi đơn bị Hủy hoặc Hoàn trả
                    if (in_array($newStatus, ['cancelled', 'returned'])) {
                        $adminEmail = config('mail.admin_address', 'admin@zyro.vn'); // Config giả định của hệ thống
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
                    // Bắt exception ở Mail để tránh làm sập luồng trả về giao diện nếu cấu hình SMTP trên server đang lỗi
                }
            }

            // Tải lại lịch sử để trả về API và Broadcast
            $order->load('histories.changer');

            broadcast(new OrderEvent('updated', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!', 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý trạng thái: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xóa mềm đơn hàng (Chỉ dùng cho đơn rác/Test)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);

            if (!in_array($order->status, ['cancelled', 'refunded', 'returned'])) {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể đưa vào thùng rác các đơn hàng đã bị Hủy hoặc Hoàn tiền.'], 400);
            }

            $order->delete();
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

            broadcast(new OrderEvent('restored', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã khôi phục đơn hàng.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xử lý Hoàn Tiền (Nghiệp vụ RMA Dành riêng cho Trang Đơn Hoàn)
     */
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
                // Ép refund_amount về 0 nếu TỪ CHỐI
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

            $order->load('histories.changer');
            broadcast(new OrderEvent('updated', $order))->toOthers();

            return response()->json(['success' => true, 'message' => 'Xử lý yêu cầu hoàn tiền thành công!', 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý hoàn tiền: ' . $e->getMessage()], 500);
        }
    }

    /**
     * HELPER: Mô phỏng lộ trình giao hàng (Tracking) một cách chân thực dựa trên khoảng cách địa lý
     */
    private function simulateTracking(Order $order): array
    {
        $events = [];
        $createdAt = Carbon::parse($order->created_at);
        $now = now();

        $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : $order->shipping_info;
        $city = $shippingInfo['city'] ?? 'Hà Nội';

        // Mặc định kho xuất phát của ZYRO ở Hà Nội
        $baseCity = $shippingInfo['origin_city'] ?? 'Hà Nội';
        $isSameCity = str_contains($city, $baseCity) || str_contains($city, 'Ha Noi');

        // Công thức tính thời gian vận chuyển mô phỏng
        $confirmTime = $createdAt->copy()->addHours(2);
        $pickupTime = $confirmTime->copy()->addHours(8);
        $transitTime = $pickupTime->copy()->addHours(12);
        // Nội thành thì 10 tiếng là đến bưu cục gốc, Tỉnh lẻ thì 36 tiếng
        $arriveLocalTime = $transitTime->copy()->addHours($isSameCity ? 10 : 36);
        $deliveryTime = $arriveLocalTime->copy()->addHours(6);
        $completeTime = clone $order->updated_at;

        // 1. Luôn có sự kiện tạo đơn (Pending)
        $events[] = [
            'time' => $createdAt->format('d/m/Y H:i'),
            'location' => 'Hệ thống ZYRO',
            'status' => 'pending',
            'description' => 'Đơn hàng được tạo thành công. Đang chờ hệ thống xác nhận.'
        ];

        // 2. Nếu đã đi qua bước Pending
        if (!in_array($order->status, ['pending', 'cancelled'])) {
            $events[] = [
                'time' => $confirmTime->format('d/m/Y H:i'),
                'location' => 'Kho ' . $baseCity,
                'status' => 'confirmed',
                'description' => 'Người bán đang chuẩn bị đóng gói hàng hóa.'
            ];
        }

        // 3. Hành trình Shipping (Đang giao)
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

            // Nếu thực tế chưa giao xong, thì check xem thời gian hiện tại đã trôi qua mốc đến kho local chưa
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

        // 4. Các điểm chốt chặn cuối cùng (Completed / Returned / Cancelled)
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

        // Đảo ngược mảng để sự kiện mới nhất lên trên cùng khi hiển thị UI cho người dùng xem
        return array_reverse($events);
    }
}
