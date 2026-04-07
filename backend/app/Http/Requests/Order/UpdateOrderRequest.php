<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_info'           => ['nullable', 'array'],
            'shipping_info.name'      => ['nullable', 'string', 'max:150'],
            'shipping_info.phone'     => ['nullable', 'string', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
            'shipping_info.address'   => ['nullable', 'string', 'max:255'],
            
            'shipping_provider'       => ['nullable', 'string', 'max:50'],
            'tracking_number'         => ['nullable', 'string', 'max:100'],
            'order_note'              => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_info.phone.regex' => 'Số điện thoại người nhận không hợp lệ.',
        ];
    }
}