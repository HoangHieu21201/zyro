<?php

// File: backend/app/Http/Controllers/Api/Admin/VoucherController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Http\Requests\Voucher\StoreVoucherRequest;
use App\Http\Requests\Voucher\UpdateVoucherRequest;
use App\Events\VoucherEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Voucher::withTrashed()->orderBy('id', 'desc');

            // Hỗ trợ bộ lọc Status, Type... trên Frontend
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            $vouchers = $query->paginate(15);
            return response()->json(['success' => true, 'data' => $vouchers]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreVoucherRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Ép mã Code viết hoa
            $data['code'] = strtoupper($data['code']);
            $data['usage_count'] = 0; // Lúc mới tạo luôn bằng 0

            $voucher = Voucher::create($data);

            broadcast(new VoucherEvent('created', $voucher))->toOthers();

            return response()->json(['success' => true, 'message' => 'Tạo mã khuyến mãi thành công!', 'data' => $voucher], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tạo mã: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $voucher = Voucher::withTrashed()->findOrFail($id);
            return response()->json(['success' => true, 'data' => $voucher]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy Voucher.'], 404);
        }
    }

    public function update(UpdateVoucherRequest $request, $id): JsonResponse
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $data = $request->validated();
            
            $data['code'] = strtoupper($data['code']);

            $voucher->update($data);

            broadcast(new VoucherEvent('updated', $voucher))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật mã khuyến mãi thành công!', 'data' => $voucher]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    // API Cập nhật nhanh trạng thái từ màn Index (Bật/Tắt)
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,hidden,expired']);
        
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->update(['status' => $request->status]);

            broadcast(new VoucherEvent('updated', $voucher))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $voucher = Voucher::findOrFail($id);

            // Gắn hậu tố để giải phóng mã Code cho việc tạo mã mới cùng tên sau này
            $voucher->code = $voucher->code . '_del_' . time();
            $voucher->save();

            $voucher->delete();

            broadcast(new VoucherEvent('deleted', $voucher))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã đưa Voucher vào thùng rác.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xóa mã: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $voucher = Voucher::withTrashed()->findOrFail($id);
            
            // Xóa hậu tố '_del_xxx' để lấy lại mã gốc
            $originalCode = preg_replace('/_del_\d+$/', '', $voucher->code);
            
            // Kiểm tra xem mã gốc có bị ai chiếm mất chưa
            if (Voucher::where('code', $originalCode)->whereNull('deleted_at')->exists()) {
                return response()->json(['success' => false, 'message' => 'Mã Code này đã bị sử dụng trong thời gian bị xóa, không thể khôi phục.'], 400);
            }

            $voucher->code = $originalCode;
            $voucher->save();
            $voucher->restore();

            broadcast(new VoucherEvent('restored', $voucher))->toOthers();

            return response()->json(['success' => true, 'message' => 'Khôi phục Voucher thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi khôi phục: ' . $e->getMessage()], 500);
        }
    }
}