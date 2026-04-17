<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVariantStockRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Bắt buộc phải rõ ràng: Đây là hành động Nhập kho (add) hay Xuất kho/Trừ hao (subtract)
            'type'     => ['required', 'in:add,subtract'],
            
            // Số lượng phải là số nguyên dương, tối thiểu 1, tối đa 10,000 (Chống gõ nhầm 1 tỷ làm sập kho)
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            
            // Lý do nhập/xuất kho (Tùy chọn)
            'note'     => ['nullable', 'string', 'max:255']
        ];
    }

    public function messages()
    {
        return [
            'type.required'    => 'Vui lòng xác định thao tác là Nhập kho hay Xuất kho.',
            'type.in'          => 'Thao tác không hợp lệ.',
            'quantity.required'=> 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min'     => 'Số lượng thay đổi ít nhất phải là 1.',
            'quantity.max'     => 'Không được phép nhập/xuất quá 10,000 sản phẩm một lúc để tránh rủi ro.',
        ];
    }
}