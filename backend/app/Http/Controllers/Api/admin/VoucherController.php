<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Http\Requests\Voucher\StoreVoucherRequest;
use App\Http\Requests\Voucher\UpdateVoucherRequest;
use App\Events\VoucherEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class VoucherController extends Controller
{
    private string $cacheVersionKey = 'vouchers_admin_cache_version';

    private function clearCache(): void
    {
        Cache::increment($this->cacheVersionKey);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $version = Cache::rememberForever($this->cacheVersionKey, fn() => 1);
            $cacheKey = sprintf('vouchers_admin_v%s_p%s_s%s', $version, $request->get('page', 1), $request->get('status', 'all'));

            $vouchers = Cache::remember($cacheKey, 86400, function() use ($request) {
                $query = Voucher::withTrashed()->orderBy('id', 'desc');

                if ($request->has('status') && $request->status !== '') {
                    $query->where('status', $request->status);
                }

                return $query->paginate(15);
            });

            return response()->json(['success' => true, 'data' => $vouchers]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreVoucherRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['code'] = strtoupper($data['code']);
            $data['usage_count'] = 0; 

            $voucher = Voucher::create($data);

            $this->clearCache();
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

            $this->clearCache();
            broadcast(new VoucherEvent('updated', $voucher))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật mã khuyến mãi thành công!', 'data' => $voucher]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,hidden,expired']);
        
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->update(['status' => $request->status]);

            $this->clearCache();
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

            $voucher->code = $voucher->code . '_del_' . time();
            $voucher->save();
            $voucher->delete();

            $this->clearCache();
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
            
            $originalCode = preg_replace('/_del_\d+$/', '', $voucher->code);
            if (Voucher::where('code', $originalCode)->whereNull('deleted_at')->exists()) {
                return response()->json(['success' => false, 'message' => 'Mã Code này đã bị sử dụng trong thời gian bị xóa, không thể khôi phục.'], 400);
            }

            $voucher->code = $originalCode;
            $voucher->save();
            $voucher->restore();

            $this->clearCache();
            broadcast(new VoucherEvent('restored', $voucher))->toOthers();

            return response()->json(['success' => true, 'message' => 'Khôi phục Voucher thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi khôi phục: ' . $e->getMessage()], 500);
        }
    }
}