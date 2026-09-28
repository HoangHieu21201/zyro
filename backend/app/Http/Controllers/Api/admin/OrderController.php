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

    public function exportExcel(Request $request)
    {
        $exportType = $request->input('export_type', 'all');
        $orderIds = $request->input('order_ids', []);
        
        $query = Order::with(['items.product', 'items.variant', 'user'])->withTrashed();
        
        if ($exportType === 'selected' && !empty($orderIds)) {
            $query->whereIn('id', $orderIds);
        } else {
            // Apply Date Filters
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Apply Statuses Array (Checkbox)
            $statuses = $request->input('statuses', []);
            if (!empty($statuses) && is_array($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        $orders = $query->orderBy('id', 'desc')->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Không có đơn hàng nào phù hợp với bộ lọc để xuất Excel!'], 400);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        
        $splitBy = $request->input('split_by', '');
        $groupedOrders = [];
        
        $statusLabels = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang chuẩn bị',
            'shipping' => 'Đang giao',
            'completed' => 'Thành công',
            'cancelled' => 'Đã hủy',
            'returned' => 'Hoàn trả',
        ];

        if ($splitBy === 'status') {
            foreach ($orders as $order) {
                $statusName = $statusLabels[$order->status] ?? ($order->status ?: 'Khác');
                $groupedOrders[$statusName][] = $order;
            }
        } elseif ($splitBy === 'month') {
            foreach ($orders as $order) {
                $month = $order->created_at->format('m-Y');
                $groupedOrders[$month][] = $order;
            }
        } else {
            $groupedOrders['Tất cả'] = $orders;
        }

        $sheetIndex = 0;
        foreach ($groupedOrders as $sheetName => $sheetOrders) {
            $safeName = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '', $sheetName);
            if (empty(trim($safeName))) $safeName = "Sheet " . ($sheetIndex + 1);
            $safeName = mb_substr(trim($safeName), 0, 31);
            
            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $safeName);
            $spreadsheet->addSheet($sheet, $sheetIndex);
            
            $titleSuffix = $splitBy ? ' - ' . strtoupper($safeName) : '';
            $sheet->setCellValue('A1', 'DANH SÁCH ĐƠN HÀNG ZYRO' . $titleSuffix);
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '547792']]
            ]);

            $columns = ['A'=>'Mã Đơn Hàng', 'B'=>'Ngày Đặt', 'C'=>'Khách Hàng', 'D'=>'Số Điện Thoại', 'E'=>'Địa Chỉ', 'F'=>'Tổng Tiền', 'G'=>'Thanh Toán', 'H'=>'Phương Thức', 'I'=>'Trạng Thái', 'J'=>'Sản Phẩm'];
            
            foreach ($columns as $col => $title) {
                $sheet->setCellValue($col.'2', $title);
            }
            
            $sheet->getStyle('A2:J2')->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(40);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(50);
            
            $rowIdx = 3;
            foreach ($sheetOrders as $order) {
                $productsList = [];
                foreach ($order->items as $item) {
                    $prodName = $item->product ? $item->product->name : 'Sản phẩm đã xóa';
                    $varName = $item->variant ? $item->variant->sku : '';
                    $priceFormatted = number_format($item->purchased_price, 0, ',', '.');
                    $productsList[] = "- $prodName ($varName) x " . $item->quantity . " [Giá: {$priceFormatted}đ]";
                }
                $productsString = implode("\n", $productsList);

                $sheet->setCellValue("A$rowIdx", $order->order_code);
                $sheet->setCellValue("B$rowIdx", $order->created_at->format('d/m/Y H:i'));
                $sheet->setCellValue("C$rowIdx", $order->shipping_info['name'] ?? ($order->user ? $order->user->full_name : 'Khách vãng lai'));
                $sheet->setCellValue("D$rowIdx", $order->shipping_info['phone'] ?? ($order->user ? $order->user->phone : ''));
                $sheet->setCellValue("E$rowIdx", $order->shipping_info['address'] ?? '');
                $sheet->setCellValue("F$rowIdx", $order->total_amount);
                $sheet->getStyle("F$rowIdx")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->setCellValue("G$rowIdx", $order->payment_status == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán');
                $sheet->setCellValue("H$rowIdx", strtoupper($order->payment_method ?? 'COD'));
                $sheet->setCellValue("I$rowIdx", $order->status);
                $sheet->setCellValue("J$rowIdx", $productsString);
                
                $sheet->getStyle("J$rowIdx")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A$rowIdx:J$rowIdx")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                
                $rowIdx++;
            }
            
            if ($rowIdx > 3) {
                $sheet->getStyle("A3:J".($rowIdx-1))->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]
                    ]
                ]);
            }
            
            $sheetIndex++;
        }
        
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = "don_hang_zyro_" . date('Ymd_His') . ".xlsx";
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
