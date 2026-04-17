<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép ai cũng được quyền gọi Request này
    }

    public function rules(): array
    {
        return [
            'fullName' => 'required|string|max:150',
            'email'    => 'required|string|email|max:150|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed', 
        ];
    }

    public function messages(): array
    {
        return [
            'fullName.required' => 'Vui lòng nhập họ và tên.',
            'fullName.max'      => 'Họ và tên không được vượt quá 150 ký tự.',
            'email.required'    => 'Vui lòng nhập địa chỉ email.',
            'email.email'       => 'Địa chỉ email không đúng định dạng.',
            'email.unique'      => 'Email này đã được đăng ký trong hệ thống.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed'=> 'Mật khẩu xác nhận không trùng khớp.',
        ];
    }
}