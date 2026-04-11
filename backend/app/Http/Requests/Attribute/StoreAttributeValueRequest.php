<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'value'        => ['required', 'string', 'min:1', 'max:100'],
            'meta_value'   => ['nullable', 'string', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'attribute_id.required' => 'Thiếu thông tin phân loại (ID Thuộc tính).',
            'attribute_id.integer'  => 'Mã phân loại không hợp lệ.',
            'attribute_id.exists'   => 'Thuộc tính cha không tồn tại trong hệ thống.',
            
            'value.required'        => 'Giá trị không được để trống.',
            'value.string'          => 'Giá trị phải là chuỗi ký tự.',
            'value.min'             => 'Giá trị phải chứa ít nhất 1 ký tự (Ví dụ: S, M, L).',
            'value.max'             => 'Giá trị không được vượt quá 100 ký tự.',
            
            'meta_value.string'     => 'Giá trị bổ sung (Meta) phải là chuỗi ký tự.',
            'meta_value.max'        => 'Giá trị bổ sung không được vượt quá 255 ký tự.',
        ];
    }
}