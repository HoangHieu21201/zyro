<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => 'required|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'variant_id.required' => 'Vui lòng chọn phân loại sản phẩm.',
            'variant_id.exists' => 'Phân loại sản phẩm không tồn tại hoặc đã bị xóa.',
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng không hợp lệ.',
            'quantity.min' => 'Số lượng tối thiểu là 1.',
            'quantity.max' => 'Bạn chỉ được mua tối đa 50 sản phẩm này trong 1 lần.',
        ];
    }
}