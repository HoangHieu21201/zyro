<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Phân quyền đã có Middleware lo
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:attributes,name']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên thuộc tính không được để trống.',
            'name.unique'   => 'Tên thuộc tính này đã tồn tại trong hệ thống.',
            'name.max'      => 'Tên quá dài (Tối đa 255 ký tự).'
        ];
    }
}