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
            'name' => ['required', 'string', 'max:255', Rule::unique('attributes', 'name')->ignore($id)]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên thuộc tính không được để trống.',
            'name.unique'   => 'Tên thuộc tính bị trùng lặp với thuộc tính khác.',
        ];
    }
}