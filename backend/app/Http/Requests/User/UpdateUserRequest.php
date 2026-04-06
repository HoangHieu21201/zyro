<?php

// File: backend/app/Http/Requests/User/UpdateUserRequest.php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'full_name' => ['required', 'string', 'max:100'],
            'email'     => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at'),
            ],
            'phone'     => [
                'nullable', 'string', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/',
                Rule::unique('users', 'phone')->ignore($userId)->whereNull('deleted_at'),
            ],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'],
            'tier_id'   => ['nullable', 'integer', 'exists:membership_tiers,id'],
            'gender'    => ['nullable', 'string', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'birthday'  => ['nullable', 'date'],
            'status'    => ['nullable', 'string', Rule::in(['active', 'locked'])],
            'avatar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_avatar' => ['nullable', 'in:true,false,1,0'],
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
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'avatar.image'       => 'Ảnh đại diện phải là hình ảnh (Tối đa 5MB).',
        ];
    }
}