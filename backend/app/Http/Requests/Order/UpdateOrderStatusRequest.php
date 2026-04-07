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
            'status'          => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled', 'refunded'])],
            'payment_status'  => ['nullable', 'string', Rule::in(['unpaid', 'paid', 'refunded'])],
            'shipping_status' => ['nullable', 'string', 'max:100'],
            
            // Note dùng để ghi log vào bảng order_status_histories
            'note'            => ['nullable', 'string', 'max:1000'], 
        ];
    }
}