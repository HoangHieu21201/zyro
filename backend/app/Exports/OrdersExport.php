<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orderIds;

    public function __construct(array $orderIds)
    {
        $this->orderIds = $orderIds;
    }

    public function collection()
    {
        return Order::whereIn('id', $this->orderIds)->get();
    }

    public function headings(): array
    {
        return [
            'Mã Đơn Hàng Zyro',
            'Tên Người Nhận',
            'Số Điện Thoại',
            'Địa Chỉ',
            'Tổng Tiền Thu Hộ (COD)',
            'Ghi Chú',
            'Hãng Vận Chuyển',
            'Mã Vận Đơn (Tracking Number)'
        ];
    }

    public function map($order): array
    {
        $shipping = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : $order->shipping_info;
        
        return [
            $order->order_code,
            $shipping['name'] ?? '',
            $shipping['phone'] ?? '',
            $shipping['address'] ?? '',
            $order->total_amount,
            $order->order_note ?? '',
            $order->shipping_provider ?? '',
            $order->tracking_number ?? ''
        ];
    }
}
