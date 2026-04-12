<?php

// File: backend/app/Http/Controllers/Api/Admin/AdminController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Events\AdminEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    private string $cacheKey = 'admin_users_list';

    // Danh sách nhân sự
    public function index(): JsonResponse
    {
        try {
            $admins = Cache::remember($this->cacheKey, 86400, function () {
                return Admin::withTrashed()->with('role')->orderBy('id', 'desc')->get();
            });

            return response()->json(['success' => true, 'data' => $admins]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    // Tạo mới Admin
    public function store(StoreAdminRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $data['email_verified_at'] = now();
            $data['status'] = $data['status'] ?? 'active';

            if ($request->hasFile('avatar')) {
                $data['avatar_url'] = $request->file('avatar')->store('avatars/admins', 'public');
            }

            $admin = Admin::create($data);
            $admin->load('role');

            Cache::forget($this->cacheKey);

            broadcast(new AdminEvent('created', $admin))->toOthers();

            return response()->json(['success' => true, 'message' => 'Tạo tài khoản thành công!', 'data' => $admin], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tạo mới: ' . $e->getMessage()], 500);
        }
    }

    // Chi tiết nhân sự
    public function show($id): JsonResponse
    {
        try {
            $admin = Admin::withTrashed()->with('role')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $admin]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản này.'], 404);
        }
    }

    //Cập nhật thông tin nhân sự
    public function update(UpdateAdminRequest $request, $id): JsonResponse
    {
        try {
            $admin = Admin::findOrFail($id);
            /** @var \App\Models\Admin|null $currentUser */
            $currentUser = $request->user();

            if ($admin->id == 1 && $currentUser?->id != 1) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền sửa tài khoản của Người sáng lập!'], 403);
            }

            $data = $request->validated();

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            if ($request->hasFile('avatar')) {
                if ($admin->avatar_url && Storage::disk('public')->exists($admin->avatar_url)) {
                    Storage::disk('public')->delete($admin->avatar_url);
                }
                $data['avatar_url'] = $request->file('avatar')->store('avatars/admins', 'public');
            } elseif (isset($data['remove_avatar']) && filter_var($data['remove_avatar'], FILTER_VALIDATE_BOOLEAN)) {
                if ($admin->avatar_url && Storage::disk('public')->exists($admin->avatar_url)) {
                    Storage::disk('public')->delete($admin->avatar_url);
                }
                $data['avatar_url'] = null;
            }

            $admin->update($data);
            $admin->load('role');

            Cache::forget($this->cacheKey);

            broadcast(new AdminEvent('updated', $admin))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật tài khoản thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $admin = Admin::findOrFail($id);
            /** @var \App\Models\Admin|null $currentUser */
            $currentUser = $request->user();

            if ($admin->id == 1) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa Người sáng lập của hệ thống!'], 403);
            }

            if ($admin->id == $currentUser?->id) {
                return response()->json(['success' => false, 'message' => 'Bạn không thể tự khóa/xóa chính mình đang thao tác!'], 400);
            }

            $admin->delete();

            Cache::forget($this->cacheKey);

            broadcast(new AdminEvent('deleted', $admin))->toOthers();
            
            return response()->json(['success' => true, 'message' => 'Đã chuyển tài khoản vào thùng rác!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xóa tài khoản: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $admin = Admin::withTrashed()->findOrFail($id);
            
            if (!$admin->role || $admin->role->deleted_at) {
                return response()->json(['success' => false, 'message' => 'Chức vụ của nhân sự này đã bị xóa khỏi hệ thống. Vui lòng khôi phục chức vụ trước!'], 422);
            }

            $admin->restore();
            $admin->load('role');

            Cache::forget($this->cacheKey);

            broadcast(new AdminEvent('restored', $admin))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã khôi phục tài khoản nhân viên thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi khôi phục: ' . $e->getMessage()], 500);
        }
    }
}