<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipTier;
use App\Http\Requests\Tier\StoreMembershipTierRequest;
use App\Http\Requests\Tier\UpdateMembershipTierRequest;
use App\Events\MembershipTierEvent;
use App\Jobs\SyncUserTiersJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class MembershipTierController extends Controller
{
    private string $cacheKey = 'membership_tiers_list';

    private function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    public function index(): JsonResponse
    {
        try {
            $tiers = Cache::remember($this->cacheKey, 86400, function () {
                return MembershipTier::withCount('users')->orderBy('min_spent', 'asc')->get();
            });
            return response()->json(['success' => true, 'data' => $tiers]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $tier = MembershipTier::findOrFail($id);
            return response()->json(['success' => true, 'data' => $tier]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin hạng thành viên.'], 404);
        }
    }

    public function store(StoreMembershipTierRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // ĐÃ BỔ SUNG: Vệ sinh dữ liệu cho trường Giới hạn giảm giá (Tránh lỗi từ FormData của Vue)
            // Nếu để trống hoặc nhập 0 -> Đưa về null (Không giới hạn)
            if (empty($data['max_discount_amount']) || $data['max_discount_amount'] <= 0) {
                $data['max_discount_amount'] = null;
            }

            if ($request->hasFile('icon')) {
                $data['icon'] = $request->file('icon')->store('tiers', 'public');
            }

            $tier = MembershipTier::create($data);

            $this->clearCache();
            
            try {
                broadcast(new MembershipTierEvent('created', $tier))->toOthers();
            } catch (\Exception $e) {
                // Bỏ qua lỗi gửi Websocket
            }

            SyncUserTiersJob::dispatch();

            return response()->json(['success' => true, 'message' => 'Thêm mới thành công! Quá trình đồng bộ hạng đang chạy ngầm.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi thêm mới: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateMembershipTierRequest $request, $id): JsonResponse
    {
        try {
            $tier = MembershipTier::findOrFail($id);
            $data = $request->validated();

            // ĐÃ BỔ SUNG: Vệ sinh dữ liệu cho trường Giới hạn giảm giá
            if (empty($data['max_discount_amount']) || $data['max_discount_amount'] <= 0) {
                $data['max_discount_amount'] = null;
            }

            if ($request->hasFile('icon')) {
                if ($tier->icon) Storage::disk('public')->delete($tier->icon);
                $data['icon'] = $request->file('icon')->store('tiers', 'public');
            } elseif (isset($data['remove_icon']) && filter_var($data['remove_icon'], FILTER_VALIDATE_BOOLEAN)) {
                if ($tier->icon) Storage::disk('public')->delete($tier->icon);
                $data['icon'] = null;
            }

            $tier->update($data);

            $this->clearCache();
            
            try {
                broadcast(new MembershipTierEvent('updated', $tier))->toOthers();
            } catch (\Exception $e) {
                // Bỏ qua lỗi gửi Websocket
            }

            SyncUserTiersJob::dispatch();

            return response()->json(['success' => true, 'message' => 'Cập nhật thành công! Quá trình đồng bộ hạng đang chạy ngầm.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $tier = MembershipTier::findOrFail($id);

            if ($tier->users()->exists()) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa! Đang có khách hàng thuộc hạng này.'], 422);
            }

            if ($tier->icon) Storage::disk('public')->delete($tier->icon);
            $tier->delete();

            $this->clearCache();
            
            try {
                broadcast(new MembershipTierEvent('deleted', $tier))->toOthers();
            } catch (\Exception $e) {
                // Bỏ qua lỗi gửi Websocket
            }

            return response()->json(['success' => true, 'message' => 'Xóa hạng thành viên thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()], 500);
        }
    }
}