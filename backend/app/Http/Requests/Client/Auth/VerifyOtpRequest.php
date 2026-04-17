<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email không được để trống.',
            'email.email'    => 'Định dạng email không hợp lệ.',
            'otp.required'   => 'Vui lòng nhập mã OTP.',
            'otp.size'       => 'Mã OTP phải bao gồm đúng 6 chữ số.',
        ];
    }
}