<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:1|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Vui lòng cung cấp số lượng cập nhật.',
            'quantity.integer' => 'Số lượng cập nhật phải là số nguyên.',
            'quantity.min' => 'Số lượng ít nhất phải là 1.',
            'quantity.max' => 'Số lượng không được vượt quá 50 sản phẩm.',
        ];
    }
}