<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Order;
use App\Models\MembershipTier;

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

            // Tính tổng tiền đã chi tiêu (Chỉ tính đơn thành công)
            $totalSpent = Order::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('total_amount');

            $user->total_spent = (float) $totalSpent;

            // Lấy danh sách toàn bộ các hạng để vẽ Roadmap
            $allTiers = MembershipTier::orderBy('min_spent', 'asc')->get();

            return response()->json([
                'success' => true, 
                'data' => $user,
                'all_tiers' => $allTiers
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $validatedData = $request->validated();

            if ($request->hasFile('avatar')) {
                if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                    Storage::disk('public')->delete($user->avatar_url);
                }
                $path = $request->file('avatar')->store('users/avatars', 'public');
                $validatedData['avatar_url'] = $path;
            }

            $user->update($validatedData);
            $user->load('tier');

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công.',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()], 500);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.'], 400);
            }

            $user->update(['password' => Hash::make($request->new_password)]);

            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            return response()->json(['success' => true, 'message' => 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }
}