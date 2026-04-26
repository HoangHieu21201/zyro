<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddLookbookCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lookbook_id' => 'required|integer|exists:lookbooks,id',
            'quantity' => 'nullable|integer|min:1|max:50',
            'lookbook_selections' => 'required|array|min:1',
            'lookbook_selections.*.product_id' => 'required|integer|exists:products,id',
            'lookbook_selections.*.variant_id' => 'required|integer|exists:product_variants,id',
            'lookbook_selections.*.quantity' => 'required|integer|min:1',
            'lookbook_selections.*.attributes' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'lookbook_id.required' => 'Mã bộ sưu tập không được để trống.',
            'lookbook_id.exists' => 'Bộ sưu tập không tồn tại trên hệ thống.',
            'lookbook_selections.required' => 'Bạn chưa chọn sản phẩm nào trong bộ sưu tập.',
            'lookbook_selections.array' => 'Định dạng dữ liệu bộ sưu tập không đúng.',
            'lookbook_selections.*.variant_id.exists' => 'Một trong các phân loại sản phẩm không hợp lệ.',
        ];
    }
}