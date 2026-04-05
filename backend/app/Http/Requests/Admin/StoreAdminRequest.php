<?php

// File: backend/app/Http/Requests/Admin/StoreAdminRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullname' => ['required', 'string', 'max:100'],
            'email'    => [
                'required', 
                'email', 
                'max:100',
                Rule::unique('admins', 'email')->whereNull('deleted_at'),
            ],
            // FIX: Thêm 'confirmed' để bắt buộc xác nhận mật khẩu
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role_id'  => ['required', 'integer', Rule::exists('roles', 'id')->whereNull('deleted_at')],
            'phone'    => ['nullable', 'string', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
            'address'  => ['nullable', 'string', 'max:255'],
            'status'   => ['nullable', 'string', Rule::in(['active', 'inactive', 'locked'])],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required'  => 'Họ tên không được để trống.',
            'email.required'     => 'Email không được để trống.',
            'email.email'        => 'Định dạng email không hợp lệ.',
            'email.unique'       => 'Email này đã được sử dụng cho một tài khoản khác đang hoạt động.',
            'password.required'  => 'Mật khẩu không được để trống.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp. Vui lòng kiểm tra lại.', // Lời nhắn cho QC
            'role_id.required'   => 'Vui lòng chọn chức vụ cho tài khoản.',
            'role_id.exists'     => 'Chức vụ này không tồn tại hoặc đã bị xóa.',
            'phone.regex'        => 'Số điện thoại không hợp lệ (Vui lòng nhập SĐT Việt Nam).',
            'avatar.image'       => 'File tải lên bắt buộc phải là hình ảnh.',
            'avatar.max'         => 'Dung lượng ảnh không được vượt quá 5MB.',
        ];
    }
}