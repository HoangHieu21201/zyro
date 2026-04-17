<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateVariantStockRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'variant_ids'   => ['required', 'array', 'min:1'],
            'variant_ids.*' => ['integer', 'exists:product_variants,id'],
            'type'          => ['required', 'in:add,subtract'],
            'quantity'      => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function messages()
    {
        return [
            'variant_ids.required' => 'Vui lòng chọn ít nhất một sản phẩm.',
            'variant_ids.array'    => 'Dữ liệu sản phẩm không hợp lệ.',
            'type.required'        => 'Vui lòng chọn thao tác Nhập hoặc Xuất.',
            'quantity.required'    => 'Vui lòng nhập số lượng.',
            'quantity.min'         => 'Số lượng ít nhất phải là 1.',
        ];
    }
}