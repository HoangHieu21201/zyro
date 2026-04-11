<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'Vui lòng nhập địa chỉ email.',
            'email.email'       => 'Định dạng email không hợp lệ.',
            'email.max'         => 'Email không được vượt quá 100 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.max'      => 'Mật khẩu vượt quá số ký tự cho phép.',
        ];
    }
}