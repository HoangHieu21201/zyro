<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'required_level' => ['required', 'integer', 'min:1', 'max:10']
        ];
    }

    public function messages(): array
    {
        return [
            'required_level.required' => 'Vui lòng cung cấp cấp độ quyền hạn.',
            'required_level.integer'  => 'Cấp độ quyền hạn phải là số nguyên.',
            'required_level.min'      => 'Cấp độ mạnh nhất là 1.',
            'required_level.max'      => 'Cấp độ yếu nhất là 10.',
        ];
    }
}
