<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $roles = Role::withCount('admins')->withTrashed()->orderBy('level', 'asc')->get();
            return response()->json(['success' => true, 'data' => $roles]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            /** @var \App\Models\Admin|null $user */
            $user = $request->user();
            $currentUserLevel = $user?->role->level ?? 999;
            
            if ($currentUserLevel >= $data['level']) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền tạo vai trò cấp độ này.'], 403);
            }

            $role = Role::create($data);

            broadcast(new \App\Events\RoleEvent('created', $role))->toOthers();

            return response()->json(['success' => true, 'message' => 'Tạo Role thành công', 'data' => $role], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);
            return response()->json(['success' => true, 'data' => $role]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy vai trò này.'], 404);
        }
    }

    public function update(UpdateRoleRequest $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);
            $data = $request->validated();

            /** @var \App\Models\Admin|null $user */
            $user = $request->user();
            $currentUserLevel = $user?->role->level ?? 999;
            
            if ($role->value === 'admin') {
                if ($data['value'] !== 'admin') {
                    return response()->json(['success' => false, 'message' => 'Không thể đổi mã hệ thống của Super Admin!'], 403);
                }
                if ($data['level'] != 1) {
                    return response()->json(['success' => false, 'message' => 'Không thể hạ cấp quyền của Super Admin!'], 403);
                }
            }

            if ($currentUserLevel >= $role->level && $currentUserLevel !== 1) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa vai trò cấp cao hơn.'], 403);
            }

            if (isset($data['level']) && $currentUserLevel >= $data['level']) {
                return response()->json(['success' => false, 'message' => 'Không thể thiết lập cấp độ cao hơn quyền của bạn.'], 403);
            }

            $role->update($data);

            broadcast(new \App\Events\RoleEvent('updated', $role))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật Role thành công', 'data' => $role]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);
            
            if ($role->value === 'admin') {
                return response()->json(['success' => false, 'message' => 'Tuyệt đối không thể xóa quyền Super Admin!'], 403);
            }

            if ($role->admins()->exists()) {
                return response()->json(['success' => false, 'message' => 'Vai trò đang được gán cho Quản trị viên, không thể xóa!'], 422);
            }

            $role->update(['value' => $role->value . '_deleted_' . time()]);
            $role->delete();

            broadcast(new \App\Events\RoleEvent('deleted', $role))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã đưa Role vào thùng rác.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $role = Role::withTrashed()->findOrFail($id);
            $originalValue = preg_replace('/_deleted_\d+$/', '', $role->value);
            
            if (Role::where('value', $originalValue)->exists()) {
                return response()->json(['success' => false, 'message' => 'Không thể khôi phục do mã vai trò đã bị tài khoản mới sử dụng.'], 422);
            }

            $role->update(['value' => $originalValue]);
            $role->restore();
            
            broadcast(new \App\Events\RoleEvent('restored', $role))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã khôi phục Role thành công', 'data' => $role]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}