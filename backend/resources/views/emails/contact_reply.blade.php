<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Phản hồi từ ZYRO</title>
</head>
<body style="font-family: 'Segoe UI', Roboto, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f8f9fa; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        
        <!-- Header Logo -->
        <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #ebf1f5; padding-bottom: 15px;">
            <h2 style="color: #213448; margin: 0; font-weight: 900; letter-spacing: 2px;">ZYRO</h2>
            <p style="color: #547792; font-size: 13px; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;">Hỗ trợ Khách hàng</p>
        </div>

        <!-- Lời chào & Trích dẫn chủ đề -->
        <div style="padding: 15px; background: #ebf1f5; border-left: 4px solid #547792; margin-bottom: 25px; border-radius: 6px;">
            <p style="margin: 0 0 8px 0; font-size: 15px;"><strong>Kính gửi:</strong> {{ $contact->name }},</p>
            <p style="margin: 0; font-size: 14px;">Chúng tôi xin gửi đến bạn phản hồi cho yêu cầu: <strong style="color: #213448;">"{{ $contact->subject }}"</strong></p>
        </div>

        <!-- Nội dung chính (Hiển thị nguyên HTML của Rich Text Editor) -->
        <div style="background: #ffffff; padding: 10px 0; margin-bottom: 30px; font-size: 15px; color: #213448;">
            {!! $replyMessage !!}
        </div>

        <!-- Trích dẫn tin nhắn gốc -->
        <div style="margin-bottom: 25px; padding-top: 20px; border-top: 1px dashed #dee2e6;">
            <p style="margin: 0 0 10px 0; font-size: 13px; color: #6c757d; font-weight: bold; text-transform: uppercase;">Nội dung tin nhắn gốc của bạn:</p>
            <blockquote style="margin: 0; padding: 12px 15px; background: #f8f9fa; border-left: 3px solid #dee2e6; color: #6c757d; font-style: italic; font-size: 14px; border-radius: 0 6px 6px 0;">
                {{ $contact->message }}
            </blockquote>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #adb5bd; border-top: 1px solid #ebf1f5; padding-top: 20px;">
            <p style="margin: 0 0 5px 0;">Đây là email gửi tự động từ hệ thống ZYRO.</p>
            <p style="margin: 0;">Nếu cần hỗ trợ khẩn cấp, vui lòng gọi Hotline: <strong style="color: #547792;">1800.9999</strong></p>
            <p style="margin: 15px 0 0 0; font-weight: bold;">&copy; {{ date('Y') }} ZYRO Store. All rights reserved.</p>
        </div>
    </div>
</body>
</html>