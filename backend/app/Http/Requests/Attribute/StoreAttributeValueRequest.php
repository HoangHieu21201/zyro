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
            'value'        => ['required', 'string', 'max:255'],
            'meta_value'   => ['nullable', 'string', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'attribute_id.required' => 'Thiếu ID Thuộc tính cha.',
            'attribute_id.exists'   => 'Thuộc tính cha không tồn tại.',
            'value.required'        => 'Giá trị không được để trống.',
        ];
    }
}