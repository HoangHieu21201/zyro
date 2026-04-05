<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Yêu cầu đặt lại mật khẩu</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border: 1px solid #EBF1F5; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        
        <h2 style="color: #213448; text-align: center; letter-spacing: 2px; margin-top: 0;">ZYRO.</h2>
        <h3 style="color: #547792; text-align: center; font-weight: normal;">Yêu cầu đặt lại mật khẩu</h3>
        
        <p style="color: #4a5568; line-height: 1.6;">Chào bạn,</p>
        <p style="color: #4a5568; line-height: 1.6;">Hệ thống nhận được yêu cầu đặt lại mật khẩu cho tài khoản quản trị viên liên kết với email này.</p>
        
        <div style="background-color: #EBF1F5; padding: 20px; text-align: center; margin: 25px 0; border-radius: 8px;">
            <p style="margin: 0; color: #4a5568; font-size: 14px; text-transform: uppercase;">Mã xác nhận (OTP) của bạn là:</p>
            <h1 style="color: #213448; font-size: 36px; margin: 15px 0; letter-spacing: 8px;">{{ $otp }}</h1>
        </div>
        
        <p style="color: #e53e3e; font-size: 13px; text-align: center; margin-top: 20px;">
            * Mã này sẽ hết hạn sau 15 phút. Tuyệt đối không chia sẻ mã này cho bất kỳ ai.
        </p>
        
        <hr style="border: none; border-top: 1px solid #EBF1F5; margin: 30px 0;" />
        
        <p style="color: #a0aec0; font-size: 12px; text-align: center; margin-bottom: 0;">
            Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email và kiểm tra lại bảo mật tài khoản.
        </p>
    </div>
</body>
</html>