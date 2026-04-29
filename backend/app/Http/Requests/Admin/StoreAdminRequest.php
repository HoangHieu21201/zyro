<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Role;

class StoreAdminRequest extends FormRequest
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
                Rule::unique('admins', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
            'role_id'  => [
                'required', 
                'integer', 
                Rule::exists('roles', 'id')->whereNull('deleted_at')
            ],
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
            'fullname.min'       => 'Họ tên quá ngắn (Tối thiểu 3 ký tự).',
            'fullname.max'       => 'Họ tên không được vượt quá 100 ký tự.',
            'email.required'     => 'Email không được để trống.',
            'email.email'        => 'Định dạng email không hợp lệ.',
            'email.unique'       => 'Email này đã được sử dụng cho một tài khoản khác đang hoạt động.',
            'password.required'  => 'Mật khẩu không được để trống.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp. Vui lòng kiểm tra lại.',
            'role_id.required'   => 'Vui lòng chọn chức vụ cho tài khoản.',
            'role_id.exists'     => 'Chức vụ này không tồn tại hoặc đã bị xóa.',
            'phone.regex'        => 'Số điện thoại không hợp lệ (Vui lòng nhập SĐT Việt Nam).',
            'avatar.image'       => 'File tải lên bắt buộc phải là hình ảnh.',
            'avatar.max'         => 'Dung lượng ảnh không được vượt quá 5MB.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roleId = $this->input('role_id');
            if (!$roleId) return;

            $currentUser = $this->user();
            $currentUserLevel = $currentUser?->role->level ?? 999;
            $targetRole = Role::find($roleId);

            if ($targetRole && $currentUserLevel >= $targetRole->level && $currentUserLevel !== 1) {
                $validator->errors()->add('role_id', 'Lỗi phân quyền: Bạn không thể cấp chức vụ ngang hoặc cao hơn quyền của mình.');
            }
        });
    }
}