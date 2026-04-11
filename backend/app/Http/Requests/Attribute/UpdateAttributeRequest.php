<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('attribute');
        
        return [
            'name' => [
                'required', 
                'string', 
                'min:2', 
                'max:50', 
                Rule::unique('attributes', 'name')->ignore($id)
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
            'name.unique'   => 'Tên thuộc tính này bị trùng lặp với một thuộc tính khác.',
        ];
    }
}