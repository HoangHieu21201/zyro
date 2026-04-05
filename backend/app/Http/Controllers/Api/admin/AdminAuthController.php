<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminRegisterRequest;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Http\Requests\Auth\AdminForgotPasswordRequest;
use App\Http\Requests\Auth\AdminResetPasswordRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class AdminAuthController extends Controller
{
    public function register(AdminRegisterRequest $request)
    {
        $data = $request->validated();
        
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        $data['status'] = $data['status'] ?? 'active';
        $data['phone'] = $data['phone'] ?? '0123456789';

        $admin = Admin::create($data);
        $admin->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Tạo tài khoản quản trị thành công',
            'data'    => $admin
        ], 201);
    }

    public function login(AdminLoginRequest $request)
    {
        $data = $request->validated();

        $admin = Admin::with('role')->where('email', $data['email'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return response()->json([
                'success' => false, 
                'message' => 'Email hoặc mật khẩu không chính xác'
            ], 401);
        }

        if ($admin->status !== 'active') {
            return response()->json([
                'success' => false, 
                'message' => 'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa'
            ], 403);
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

    public function forgotPassword(AdminForgotPasswordRequest $request)
    {
        $email = $request->validated('email');
        
        $otp = (string) random_int(100000, 999999);

        // Lưu mã OTP vào database (tự update nếu đã gửi trước đó)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp,
                'created_at' => Carbon::now()
            ]
        );

        // Try - Catch để bắt lỗi gửi Mail (tránh sập app nếu sai config)
        try {
            // Gửi email dạng HTML cho chuyên nghiệp
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
                    <p style='color: #e53e3e; font-size: 13px; text-align: center;'>* Mã này sẽ hết hạn sau 15 phút. Tuyệt đối không chia sẻ mã này cho bất kỳ ai.</p>
                    <hr style='border: none; border-top: 1px solid #EBF1F5; margin: 30px 0;' />
                    <p style='color: #a0aec0; font-size: 12px; text-align: center;'>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.</p>
                </div>
            ";

            Mail::html($htmlContent, function ($message) use ($email) {
                $message->to($email)
                        ->subject('Mã xác nhận đặt lại mật khẩu (OTP) - Zyro Admin');
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cấu hình gửi mail. Không thể gửi OTP lúc này. (' . $e->getMessage() . ')'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mã xác nhận đã được gửi đến email của bạn.'
        ]);
    }

    public function resetPassword(AdminResetPasswordRequest $request)
    {
        $data = $request->validated();
        
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->where('token', $data['token'])
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác nhận không hợp lệ hoặc sai số.'
            ], 400);
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return response()->json([
                'success' => false,
                'message' => 'Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.'
            ], 400);
        }

        $admin = Admin::where('email', $data['email'])->first();
        $admin->update([
            'password' => Hash::make($data['password'])
        ]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        $admin->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu đã được cập nhật thành công.'
        ]);
    }

    public function me(Request $request)
    {
        $admin = $request->user()->load('role');
        
        return response()->json([
            'success' => true,
            'data'    => $admin
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }
}