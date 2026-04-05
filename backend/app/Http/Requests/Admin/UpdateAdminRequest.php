<?php

// File: backend/app/Http/Requests/Admin/UpdateAdminRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminId = $this->route('admin');

        return [
            'fullname' => ['required', 'string', 'max:100'],
            'email'    => [
                'required', 
                'email', 
                'max:100',
                Rule::unique('admins', 'email')->ignore($adminId)->whereNull('deleted_at'),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role_id'  => ['required', 'integer', Rule::exists('roles', 'id')->whereNull('deleted_at')],
            'phone'    => ['nullable', 'string', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
            'address'  => ['nullable', 'string', 'max:255'],
            'status'   => ['nullable', 'string', Rule::in(['active', 'inactive', 'locked'])],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_avatar' => ['nullable', 'in:true,false,1,0'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required'  => 'Họ tên không được để trống.',
            'email.required'     => 'Email không được để trống.',
            'email.unique'       => 'Email này đã bị trùng lặp với nhân sự khác.',
            'password.min'       => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp. Vui lòng kiểm tra lại.', // Lời nhắn cho QC
            'role_id.exists'     => 'Chức vụ không tồn tại.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
            'avatar.max'         => 'Dung lượng ảnh không được vượt quá 5MB.',
        ];
    }
}