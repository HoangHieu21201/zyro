<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Khóa chặt dữ liệu mảng giao hàng
            'shipping_info'           => ['nullable', 'array'],
            'shipping_info.name'      => ['nullable', 'string', 'min:2', 'max:150'],
            'shipping_info.phone'     => ['nullable', 'string', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
            'shipping_info.address'   => ['nullable', 'string', 'min:5', 'max:255'],
            
            // Khóa chặt dữ liệu vận chuyển tránh nhập ký tự rác
            'shipping_provider'       => ['nullable', 'string', 'min:2', 'max:50'],
            'tracking_number'         => ['nullable', 'string', 'min:4', 'max:100'],
            'order_note'              => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_info.name.min'    => 'Tên người nhận quá ngắn.',
            'shipping_info.name.max'    => 'Tên người nhận quá dài (Tối đa 150 ký tự).',
            
            'shipping_info.phone.regex' => 'Số điện thoại người nhận không hợp lệ.',
            
            'shipping_info.address.min' => 'Địa chỉ người nhận quá ngắn, vui lòng nhập chi tiết hơn.',
            'shipping_info.address.max' => 'Địa chỉ người nhận vượt quá 255 ký tự.',
            
            'shipping_provider.min'     => 'Tên đơn vị vận chuyển quá ngắn.',
            'shipping_provider.max'     => 'Tên đơn vị vận chuyển vượt quá 50 ký tự.',
            
            'tracking_number.min'       => 'Mã vận đơn quá ngắn (Tối thiểu 4 ký tự).',
            'tracking_number.max'       => 'Mã vận đơn vượt quá 100 ký tự.',
            
            'order_note.max'            => 'Ghi chú đơn hàng không được vượt quá 1000 ký tự.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $orderId = $this->route('id') ?? $this->route('order');
            $order = Order::find($orderId);

            if (!$order) return;

            // BẢO MẬT HÀNG HÓA: Các trạng thái đã "Rời kho" hoặc "Đóng" không được phép đổi thông tin vận chuyển
            $lockedStatuses = ['shipping', 'completed', 'returned', 'cancelled'];

            if (in_array($order->status, $lockedStatuses)) {
                
                // Cố tình đẩy lên mã tracking mới khác với database
                if ($this->has('tracking_number') && $this->input('tracking_number') !== $order->tracking_number) {
                    $validator->errors()->add('tracking_number', 'Cảnh báo Pháp lý: Đơn hàng đã xuất kho, hệ thống khóa thay đổi Mã vận đơn.');
                }
                
                // Cố tình đổi hãng giao hàng
                if ($this->has('shipping_provider') && $this->input('shipping_provider') !== $order->shipping_provider) {
                    $validator->errors()->add('shipping_provider', 'Cảnh báo Pháp lý: Đơn hàng đã xuất kho, hệ thống khóa thay đổi Đơn vị vận chuyển.');
                }
            }
        });
    }
}