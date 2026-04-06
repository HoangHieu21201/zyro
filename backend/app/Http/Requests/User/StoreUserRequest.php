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
            'full_name' => ['required', 'string', 'max:100'],
            'email'     => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone'     => [
                'nullable', 'string', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/',
                Rule::unique('users', 'phone')->whereNull('deleted_at'),
            ],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'tier_id'   => ['nullable', 'integer', 'exists:membership_tiers,id'],
            'gender'    => ['nullable', 'string', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'birthday'  => ['nullable', 'date'],
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
            'email.required'     => 'Email không được để trống.',
            'email.unique'       => 'Email này đã được sử dụng bởi khách hàng khác.',
            'phone.unique'       => 'Số điện thoại này đã được đăng ký.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'avatar.image'       => 'Ảnh đại diện phải là hình ảnh (Tối đa 5MB).',
        ];
    }
}