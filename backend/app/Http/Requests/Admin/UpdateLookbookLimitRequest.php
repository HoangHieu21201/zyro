<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLookbookLimitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usage_limit' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }

    public function messages()
    {
        return [
            'usage_limit.integer' => 'Giới hạn phải là số nguyên.',
            'usage_limit.min'     => 'Giới hạn không được nhỏ hơn 0.',
        ];
    }
}