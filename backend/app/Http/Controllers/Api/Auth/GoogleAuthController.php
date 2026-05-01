<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * CHUYỂN HƯỚNG SANG GOOGLE (FIX CORS)
     */
    public function redirect()
    {
        // Stateless là bắt buộc khi dùng API
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        
        return response()->json([
            'success' => true,
            'url' => $url
        ]);
    }

    /**
     * XỬ LÝ KẾT QUẢ TỪ GOOGLE TRẢ VỀ (FIX BẢO MẬT KHÔNG XÓA PASS CŨ)
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Nếu khách đã từng tạo tài khoản bằng tay -> CHỈ CẬP NHẬT google_id, TUYỆT ĐỐI KHÔNG SET PASSWORD = NULL
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar_url' => $user->avatar_url ?? $googleUser->avatar
                ]);
            } else {
                // Nếu chưa từng có tài khoản -> Tạo mới với mật khẩu ngẫu nhiên để chống hack
                $user = User::create([
                    'full_name' => $googleUser->name, // Đồng bộ trường full_name với hàm Register
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar_url' => $googleUser->avatar,
                    'password' => bcrypt(Str::random(24)),
                    'status' => 'active'
                ]);
            }

            // Tạo Token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Chuyển thẳng về trang Login của Frontend kèm Token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/login?token=' . $token;
            
            return redirect()->away($frontendUrl);

        } catch (Exception $e) {
            return redirect()->away(env('FRONTEND_URL', 'http://localhost:5173') . '/login?error=google_auth_failed');
        }
    }
}