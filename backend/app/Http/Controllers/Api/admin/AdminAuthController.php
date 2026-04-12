<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminRegisterRequest;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Http\Requests\Auth\AdminForgotPasswordRequest;
use App\Http\Requests\Auth\AdminResetPasswordRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class AdminAuthController extends Controller
{
    // Đăng ký tài khoản
    public function register(AdminRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        $data['status'] = $data['status'] ?? 'active';
        $data['phone'] = $data['phone'] ?? '0123456789';

        $admin = Admin::create($data);
        $admin->load('role');

        return response()->json(['success' => true, 'message' => 'Tạo tài khoản thành công', 'data' => $admin], 201);
    }

    // Đăng nhập hệ thống
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $admin = Admin::with('role')->where('email', $data['email'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return response()->json(['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'], 401);
        }

        if ($admin->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa'], 403);
        }

        $abilities = $admin->role ? ['level:' . $admin->role->level] : ['level:5'];
        $token = $admin->createToken('admin_token', $abilities)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'token'   => $token,
            'admin'   => $admin 
        ]);
    }

    // Gửi OTP Quên mật khẩu (Lưu vào Redis Cache 15 phút)
    public function forgotPassword(AdminForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $otp = (string) random_int(100000, 999999);

        Cache::put('admin_pwd_reset_' . $email, $otp, now()->addMinutes(15));

        try {
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #EBF1F5; border-radius: 10px;'>
                    <h2 style='color: #213448; text-align: center; letter-spacing: 2px;'>ZYRO.</h2>
                    <h3 style='color: #547792; text-align: center;'>Yêu cầu đặt lại mật khẩu</h3>
                    <p style='color: #4a5568;'>Chào bạn,</p>
                    <p style='color: #4a5568;'>Hệ thống nhận được yêu cầu đặt lại mật khẩu cho tài khoản quản trị viên liên kết với email này.</p>
                    <div style='background-color: #EBF1F5; padding: 15px; text-align: center; margin: 20px 0; border-radius: 5px;'>
                        <p style='margin: 0; color: #4a5568; font-size: 14px;'>Mã xác nhận (OTP) của bạn là:</p>
                        <h1 style='color: #213448; font-size: 32px; margin: 10px 0; letter-spacing: 5px;'>{$otp}</h1>
                    </div>
                    <p style='color: #e53e3e; font-size: 13px; text-align: center;'>* Mã này sẽ tự động hủy sau 15 phút.</p>
                    <hr style='border: none; border-top: 1px solid #EBF1F5; margin: 30px 0;' />
                    <p style='color: #a0aec0; font-size: 12px; text-align: center;'>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.</p>
                </div>
            ";

            Mail::html($htmlContent, function ($message) use ($email) {
                $message->to($email)->subject('Mã xác nhận đặt lại mật khẩu (OTP) - Zyro Admin');
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cấu hình gửi mail.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Mã xác nhận đã được gửi đến email của bạn.']);
    }

    // Đặt lại mật khẩu (Kiểm tra bằng Redis)
    public function resetPassword(AdminResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $cachedOtp = Cache::get('admin_pwd_reset_' . $data['email']);

        if (!$cachedOtp) {
            return response()->json(['success' => false, 'message' => 'Mã xác nhận đã hết hạn hoặc không tồn tại.'], 400);
        }

        if ($cachedOtp !== $data['token']) {
            return response()->json(['success' => false, 'message' => 'Mã xác nhận không hợp lệ.'], 400);
        }

        $admin = Admin::where('email', $data['email'])->first();
        $admin->update(['password' => Hash::make($data['password'])]);

        Cache::forget('admin_pwd_reset_' . $data['email']);
        $admin->tokens()->delete(); 

        return response()->json(['success' => true, 'message' => 'Mật khẩu đã được cập nhật thành công.']);
    }

    // Lấy thông tin cá nhân
    public function me(Request $request): JsonResponse
    {
        $admin = $request->user()->load('role');
        return response()->json(['success' => true, 'data' => $admin]);
    }

    // Đăng xuất
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Đăng xuất thành công']);
    }
}