<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Định dạng email không hợp lệ.',
            'email.exists'   => 'Email này chưa được đăng ký trong hệ thống ZYRO.',
        ];
    }
}