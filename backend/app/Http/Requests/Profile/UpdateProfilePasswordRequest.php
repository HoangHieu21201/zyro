<?php

// File: backend/app/Http/Requests/Profile/UpdateProfilePasswordRequest.php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // CHỐNG TẤN CÔNG BCRYPT DOS: Bắt buộc giới hạn max:255
            'current_password' => ['required', 'string', 'max:255'],
            'password'         => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.max'      => 'Mật khẩu hiện tại vượt quá độ dài hệ thống.',
            'password.required'         => 'Vui lòng nhập mật khẩu mới.',
            'password.min'              => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.max'              => 'Mật khẩu mới vượt quá giới hạn (Max 255).',
            'password.confirmed'        => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }
}