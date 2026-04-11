<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::exists('admins', 'email')->whereNull('deleted_at')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Định dạng email không hợp lệ.',
            'email.exists'   => 'Không tìm thấy tài khoản quản trị viên nào liên kết với email này.',
        ];
    }
}
