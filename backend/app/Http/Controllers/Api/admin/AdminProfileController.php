<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileInfoRequest;
use App\Http\Requests\Profile\UpdateProfilePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Events\AdminEvent;

class AdminProfileController extends Controller
{
    // Cập nhật thông tin cá nhân
    public function updateInfo(UpdateProfileInfoRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\Admin $admin */
            $admin = $request->user();
            $data = $request->validated();

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

            // Xóa cache danh sách Admin dùng chung
            Cache::forget('admin_users_list');

            broadcast(new AdminEvent('updated', $admin))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật hồ sơ thành công!', 'data' => $admin]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // Đổi mật khẩu cá nhân
    public function updatePassword(UpdateProfilePasswordRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\Admin $admin */
            $admin = $request->user();
            $data = $request->validated();

            if (!Hash::check($data['current_password'], $admin->password)) {
                return response()->json([
                    'success' => false, 
                    'errors' => ['current_password' => ['Mật khẩu hiện tại không chính xác.']]
                ], 422);
            }

            $admin->update(['password' => Hash::make($data['password'])]);

            return response()->json(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}