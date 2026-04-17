<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style='color: #dc3545;'>CẢNH BÁO ĐƠN HÀNG BẤT THƯỜNG</h2>
        <p>Đơn hàng <strong>{{ $order->order_code }}</strong> vừa bị chuyển sang trạng thái <strong style='text-transform: uppercase;'>{{ $status }}</strong>.</p>
        <p>Người thực hiện thao tác: <strong>{{ $adminName }}</strong></p>
        <p>Lý do: {{ $note }}</p>
        <p>Vui lòng kiểm tra trên hệ thống quản trị ngay lập tức để xử lý khiếu nại (nếu có) và đối soát kho hàng.</p>
    </div>
</body>
</html>