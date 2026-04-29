<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class MergeCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'local_items' => 'present|array',
            'local_items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'local_items.*.quantity' => 'required|integer|min:1|max:50',
            // ĐÃ BỔ SUNG: Cho phép truyền thêm dữ liệu combo để đồng bộ an toàn
            'local_items.*.lookbook_id' => 'nullable|integer|exists:lookbooks,id',
            'local_items.*.lookbook_selections' => 'nullable|array',
        ];
    }
}