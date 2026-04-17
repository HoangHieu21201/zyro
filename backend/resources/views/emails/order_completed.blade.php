<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #213448; text-align: center; padding: 30px 20px; color: #fff; }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 3px; color: #fff; font-weight: bold; }
        .body { padding: 30px; text-align: center; }
        .body h2 { color: #547792; margin-top: 0; }
        .footer { text-align: center; padding: 25px 20px; font-size: 12px; color: #888; background-color: #fcfcfc; border-top: 1px solid #eeeeee; }
        .btn { display: inline-block; margin-top: 20px; padding: 12px 25px; background-color: #213448; color: #ffffff !important; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>ZYRO.</h1>
        </div>
        <div class="body">
            <h2>Cảm ơn bạn đã lựa chọn ZYRO!</h2>
            <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
            <p>Tuyệt vời! Đơn hàng <strong>#{{ $order->order_code }}</strong> đã được giao thành công đến tay bạn.</p>
            <p>ZYRO hy vọng bạn sẽ có những outfit thật phong cách và tự tin với các sản phẩm vừa nhận được. Sự hài lòng của bạn là động lực lớn nhất của chúng tôi.</p>
            <p>Nếu sản phẩm có vấn đề về size số hoặc lỗi do nhà sản xuất, đừng ngần ngại liên hệ với ZYRO trong vòng 7 ngày để được hỗ trợ đổi trả nhé.</p>
            
            <a href="http://localhost:5173/shop" class="btn">Tiếp tục mua sắm</a>
        </div>
        <div class="footer">
            <p>Trân trọng,<br>Đội ngũ ZYRO Boutique</p>
        </div>
    </div>
</body>
</html>