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
            'full_name' => ['required', 'string', 'min:3', 'max:100'],
            'email'     => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at'),
            ],
            'phone'     => [
                'nullable',
                'string',
                'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/',
                Rule::unique('users', 'phone')->ignore($userId)->whereNull('deleted_at'),
            ],
            'password'  => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
            'tier_id'   => [
                'nullable',
                'integer',
                Rule::exists('membership_tiers', 'id')->whereNull('deleted_at')
            ],
            'gender'    => ['nullable', 'string', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'birthday'  => ['nullable', 'date', 'before:today'],
            'status'    => ['nullable', 'string', Rule::in(['active', 'locked'])],
            'avatar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_avatar' => ['nullable', 'in:true,false,1,0'],
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
            'email.unique'       => 'Email này đã bị người khác sử dụng.',

            'phone.unique'       => 'Số điện thoại này đã được đăng ký bởi người khác.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',

            'password.min'       => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.max'       => 'Mật khẩu vượt quá giới hạn.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',

            'tier_id.exists'     => 'Hạng thành viên không tồn tại hoặc đã bị xóa.',
            'birthday.before'    => 'Ngày sinh không thể là một ngày trong tương lai.',

            'avatar.image'       => 'Ảnh đại diện phải là hình ảnh.',
            'avatar.mimes'       => 'Ảnh đại diện chỉ hỗ trợ định dạng: jpeg, png, jpg, webp.',
            'avatar.max'         => 'Ảnh đại diện tối đa 5MB.',
        ];
    }
}
