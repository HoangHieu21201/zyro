<?php

// File: backend/app/Http/Requests/User/StoreUserRequest.php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:100'],
            'email'     => [
                'required', 
                'string',
                'email', 
                'max:150',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone'     => [
                'nullable', 
                'string', 
                'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/',
                Rule::unique('users', 'phone')->whereNull('deleted_at'),
            ],
            'password'  => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
            'tier_id'   => [
                'nullable', 
                'integer', 
                Rule::exists('membership_tiers', 'id')->whereNull('deleted_at')
            ],
            'gender'    => ['nullable', 'string', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'birthday'  => ['nullable', 'date', 'before:today'],
            'status'    => ['nullable', 'string', Rule::in(['active', 'locked'])],
            'avatar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            
            // Địa chỉ mặc định (Nếu có nhập)
            'shipping_address' => ['nullable', 'string', 'max:255'],
            'city'             => ['nullable', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'],
            'ward'             => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ và tên không được để trống.',
            'full_name.min'      => 'Họ và tên quá ngắn (Tối thiểu 3 ký tự).',
            'full_name.max'      => 'Họ và tên không được vượt quá 100 ký tự.',
            
            'email.required'     => 'Email không được để trống.',
            'email.email'        => 'Định dạng email không hợp lệ.',
            'email.unique'       => 'Email này đã được sử dụng bởi khách hàng khác.',
            
            'phone.unique'       => 'Số điện thoại này đã được đăng ký.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
            
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.max'       => 'Mật khẩu vượt quá giới hạn.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            
            'tier_id.exists'     => 'Hạng thành viên không tồn tại hoặc đã bị xóa.',
            'birthday.before'    => 'Ngày sinh không thể là ngày trong tương lai.',
            
            'avatar.image'       => 'Ảnh đại diện phải là hình ảnh.',
            'avatar.mimes'       => 'Ảnh đại diện chỉ hỗ trợ định dạng: jpeg, png, jpg, webp.',
            'avatar.max'         => 'Ảnh đại diện tối đa 5MB.',
        ];
    }
}