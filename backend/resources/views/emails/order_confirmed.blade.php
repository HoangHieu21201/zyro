<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #ffffff; text-align: center; padding: 30px 20px; border-bottom: 4px solid #213448; }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 3px; color: #213448; font-weight: bold; }
        .body { padding: 30px; }
        .success-box { background-color: #e6f9f0; border-left: 4px solid #009981; padding: 15px; margin-bottom: 25px; border-radius: 4px; }
        .footer { text-align: center; padding: 25px 20px; font-size: 12px; color: #888; background-color: #fcfcfc; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>ZYRO.</h1>
        </div>
        <div class="body">
            <div class="success-box">
                <p style="margin: 0; color: #007a67; font-weight: bold; font-size: 16px;">Đơn hàng của bạn đã được xác nhận!</p>
            </div>
            <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
            <p>ZYRO xin thông báo đơn hàng <strong>#{{ $order->order_code }}</strong> của bạn đã được hệ thống xác nhận và đang được chuyển sang bộ phận kho.</p>
            <p>Chúng tôi đang tiến hành kiểm tra chất lượng, đóng gói cẩn thận những sản phẩm thời trang bạn đã chọn và sẽ sớm bàn giao cho đơn vị vận chuyển.</p>
            <p>Bạn có thể theo dõi tiến độ giao hàng chi tiết trên website của chúng tôi.</p>
        </div>
        <div class="footer">
            <p>Đội ngũ ZYRO Boutique</p>
            <p>Luôn mang đến cho bạn những phong cách thời trang dẫn đầu.</p>
        </div>
    </div>
</body>
</html>