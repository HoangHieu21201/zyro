<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullname' => ['required', 'string', 'min:3', 'max:100'],
            
            'email'    => [
                'required', 
                'string', 
                'email', 
                'max:100', 
                Rule::unique('admins', 'email')->whereNull('deleted_at')
            ],
            
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            
            'role_id'  => [
                'required', 
                'integer', 
                Rule::exists('roles', 'id')->whereNull('deleted_at')
            ],
            
            'status'   => ['required', 'string', Rule::in(['active', 'inactive', 'locked'])]
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required'  => 'Vui lòng nhập họ và tên.',
            'fullname.min'       => 'Họ và tên quá ngắn (Tối thiểu 3 ký tự).',
            'fullname.max'       => 'Họ và tên không được vượt quá 100 ký tự.',
            
            'email.required'     => 'Vui lòng nhập địa chỉ email.',
            'email.email'        => 'Địa chỉ email không hợp lệ.',
            'email.unique'       => 'Email này đã tồn tại trong hệ thống hoặc đang bị khóa.',
            
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp.',
            
            'role_id.required'   => 'Vui lòng chọn phân quyền cho tài khoản.',
            'role_id.exists'     => 'Quyền truy cập không tồn tại hoặc đã bị gỡ bỏ.',
            
            'status.in'          => 'Trạng thái tài khoản không hợp lệ.'
        ];
    }
}