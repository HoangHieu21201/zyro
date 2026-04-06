<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipTier;
use App\Http\Requests\Tier\StoreMembershipTierRequest;
use App\Http\Requests\Tier\UpdateMembershipTierRequest;
use App\Events\MembershipTierEvent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class MembershipTierController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Sắp xếp theo mức chi tiêu từ thấp lên cao (Đồng -> Bạc -> Vàng)
            $tiers = MembershipTier::withCount('users')->orderBy('min_spent', 'asc')->get();
            return response()->json(['success' => true, 'data' => $tiers]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreMembershipTierRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('icon')) {
                $data['icon'] = $request->file('icon')->store('tiers/icons', 'public');
            }

            $tier = MembershipTier::create($data);

            broadcast(new MembershipTierEvent('created', $tier))->toOthers();

            return response()->json(['success' => true, 'message' => 'Thêm hạng thành viên thành công!', 'data' => $tier], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $tier = MembershipTier::findOrFail($id);
            return response()->json(['success' => true, 'data' => $tier]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hạng này.'], 404);
        }
    }

    public function update(UpdateMembershipTierRequest $request, $id): JsonResponse
    {
        try {
            $tier = MembershipTier::findOrFail($id);
            $data = $request->validated();

            if ($request->hasFile('icon')) {
                if ($tier->icon) Storage::disk('public')->delete($tier->icon);
                $data['icon'] = $request->file('icon')->store('tiers/icons', 'public');
            } elseif (isset($data['remove_icon']) && filter_var($data['remove_icon'], FILTER_VALIDATE_BOOLEAN)) {
                if ($tier->icon) Storage::disk('public')->delete($tier->icon);
                $data['icon'] = null;
            }

            $tier->update($data);

            broadcast(new MembershipTierEvent('updated', $tier))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $tier = MembershipTier::findOrFail($id);

            // Chặn xóa nếu đang có khách hàng thuộc hạng này
            if ($tier->users()->exists()) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa! Đang có khách hàng thuộc hạng này.'], 422);
            }

            if ($tier->icon) Storage::disk('public')->delete($tier->icon);
            $tier->delete();

            broadcast(new MembershipTierEvent('deleted', $tier))->toOthers();

            return response()->json(['success' => true, 'message' => 'Xóa hạng thành viên thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()], 500);
        }
    }
}