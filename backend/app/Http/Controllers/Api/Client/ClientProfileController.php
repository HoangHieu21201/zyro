<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

// KHAI BÁO FORM REQUEST MỚI TẠO Ở ĐÂY
use App\Http\Requests\Client\User\UpdateProfileRequest;
use App\Http\Requests\Client\User\ChangePasswordRequest;

class ClientProfileController extends Controller
{
    public function getProfile()
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $user->load('tier');

            return response()->json(['success' => true, 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }

    // ĐÃ FIX: Dùng Form Request mới để bắt lỗi tự động
    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Lấy dữ liệu đã được lọc sạch sẽ
            $validatedData = $request->validated();

            // XỬ LÝ UPLOAD ẢNH AVATAR
            if ($request->hasFile('avatar')) {
                // Xóa ảnh cũ nếu có (trừ khi nó là ảnh mặc định không nằm trong storage/users/avatars)
                if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                    Storage::disk('public')->delete($user->avatar_url);
                }

                // Lưu ảnh mới vào thư mục storage/app/public/users/avatars
                $path = $request->file('avatar')->store('users/avatars', 'public');
                $validatedData['avatar_url'] = $path;
            }

            // Cập nhật Database
            $user->update($validatedData);
            $user->load('tier');

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công.',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi CSDL: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (! Hash::check($request->current_password, $user->password)) {
                // Mật khẩu hiện tại gõ sai -> Trả về mã lỗi 400
                return response()->json(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.'], 400);
            }

            // Đổi mật khẩu
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Cực kỳ quan trọng: Xóa tất cả token cũ để ép thiết bị bị hack phải đăng xuất
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi Server: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
