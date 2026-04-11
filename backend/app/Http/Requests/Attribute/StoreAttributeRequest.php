<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Phân quyền đã có Middleware lo
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 
                'string', 
                'min:2', 
                'max:50', 
                Rule::unique('attributes', 'name')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên thuộc tính không được để trống.',
            'name.string'   => 'Tên thuộc tính phải là chuỗi ký tự.',
            'name.min'      => 'Tên thuộc tính quá ngắn (Tối thiểu 2 ký tự).',
            'name.max'      => 'Tên thuộc tính không được vượt quá 50 ký tự.',
            'name.unique'   => 'Tên thuộc tính này đã tồn tại trong hệ thống.',
        ];
    }
}