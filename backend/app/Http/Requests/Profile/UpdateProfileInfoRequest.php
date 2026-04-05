<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy ID của người đang đăng nhập
        $userId = $this->user()->id;

        return [
            'fullname' => ['required', 'string', 'max:100'],
            'email'    => [
                'required', 
                'email', 
                'max:100',
                // Không được trùng với email của người khác (bỏ qua chính mình)
                Rule::unique('admins', 'email')->ignore($userId)->whereNull('deleted_at'),
            ],
            'phone'    => ['nullable', 'string', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
            'address'  => ['nullable', 'string', 'max:255'],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_avatar' => ['nullable', 'in:true,false,1,0'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Họ tên không được để trống.',
            'email.required'    => 'Email không được để trống.',
            'email.unique'      => 'Email này đã bị người khác sử dụng.',
            'phone.regex'       => 'Số điện thoại không hợp lệ.',
            'avatar.image'      => 'File phải là hình ảnh.',
            'avatar.max'        => 'Dung lượng ảnh tối đa 5MB.',
        ];
    }
}