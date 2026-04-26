<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Order;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Bổ sung 'returned' vào danh sách trạng thái hợp lệ
            'status'          => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled', 'refunded', 'returned'])],
            'payment_status'  => ['nullable', 'string', Rule::in(['unpaid', 'paid', 'refunded'])],
            'shipping_status' => ['nullable', 'string', 'max:100'],
            
            // Note dùng để ghi log vào bảng order_status_histories
            'note'            => ['nullable', 'string', 'max:1000'], 
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'             => 'Trạng thái đơn hàng không hợp lệ.',
            'payment_status.in'     => 'Trạng thái thanh toán không hợp lệ.',
            'shipping_status.max'   => 'Trạng thái giao hàng không được vượt quá 100 ký tự.',
            'note.max'              => 'Ghi chú cập nhật không được vượt quá 1000 ký tự.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $orderId = $this->route('id') ?? $this->route('order');
            $order = Order::find($orderId);

            if (!$order) return;

            $newPaymentStatus = $this->input('payment_status');

            // 1. BẢO MẬT TÀI CHÍNH: Cấm lùi trạng thái từ 'paid' (Đã thanh toán) về 'unpaid'
            if ($order->payment_status === 'paid' && $newPaymentStatus === 'unpaid') {
                $validator->errors()->add('payment_status', 'Cảnh báo Tài chính: Nghiêm cấm lùi trạng thái từ Đã thanh toán về Chưa thanh toán.');
            }

            // 2. BẢO MẬT QUY TRÌNH: Trạng thái 'refunded' (Hoàn tiền) chỉ được phép đi qua đường vòng (Module Xử lý RMA)
            if ($order->payment_status !== 'refunded' && $newPaymentStatus === 'refunded') {
                $validator->errors()->add('payment_status', 'Lỗi Quy trình: Việc hoàn tiền (Refund) bắt buộc phải được xử lý và ghi nhận qua Module Kế toán (Hoàn Trả / RMA).');
            }
        });
    }
}