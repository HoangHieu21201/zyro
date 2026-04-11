<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
}