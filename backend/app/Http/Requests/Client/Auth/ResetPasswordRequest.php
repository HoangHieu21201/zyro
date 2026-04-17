<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email'       => ['required', 'email'],
            'reset_token' => ['required', 'string'],
            'password'    => [
                'required', 
                'confirmed', 
                // Ép chuẩn bảo mật: Tối thiểu 8 ký tự, ít nhất 1 chữ cái và 1 số
                Password::min(8)->letters()->numbers() 
            ],
        ];
    }

    public function messages()
    {
        return [
            'email.required'       => 'Email bị thiếu trong yêu cầu.',
            'reset_token.required' => 'Mã xác thực bảo mật (Token) bị thiếu.',
            'password.required'    => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed'   => 'Xác nhận mật khẩu không khớp.',
            'password.min'         => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.letters'     => 'Mật khẩu phải chứa ít nhất 1 chữ cái.',
            'password.numbers'     => 'Mật khẩu phải chứa ít nhất 1 chữ số.',
        ];
    }
}