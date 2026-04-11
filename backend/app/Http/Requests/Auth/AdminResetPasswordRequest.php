<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => [
                'required',
                'string',
                'email',
                Rule::exists('admins', 'email')->whereNull('deleted_at')
            ],
            'token'    => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'     => 'Thông tin email bị thiếu.',
            'email.exists'       => 'Email xác thực không hợp lệ.',
            'token.required'     => 'Vui lòng nhập mã xác nhận (OTP).',
            'token.size'         => 'Mã OTP phải bao gồm chính xác 6 ký tự.',
            'password.required'  => 'Vui lòng nhập mật khẩu mới.',
            'password.min'       => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không trùng khớp.',
        ];
    }
}
