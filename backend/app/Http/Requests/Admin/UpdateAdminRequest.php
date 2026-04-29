<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Admin;
use App\Models\Role;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminId = $this->route('admin') ?? $this->route('id');

        return [
            'fullname' => ['required', 'string', 'min:3', 'max:100'],
            'email'    => [
                'required', 
                'string',
                'email', 
                'max:100',
                Rule::unique('admins', 'email')->ignore($adminId)->whereNull('deleted_at'),
            ],
            'password' => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
            'role_id'  => [
                'required', 
                'integer', 
                Rule::exists('roles', 'id')->whereNull('deleted_at')
            ],
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
            'fullname.min'       => 'Họ tên quá ngắn (Tối thiểu 3 ký tự).',
            'email.unique'       => 'Email này đã bị trùng lặp với nhân sự khác.',
            'password.min'       => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'role_id.required'   => 'Vui lòng chọn chức vụ cho tài khoản.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $adminId = $this->route('admin') ?? $this->route('id');
            $targetAdmin = Admin::find($adminId);
            
            if (!$targetAdmin) return;

            $currentUser = $this->user();
            $currentUserLevel = $currentUser?->role->level ?? 999;
            $newRoleId = $this->input('role_id');

            if ($newRoleId && $newRoleId != $targetAdmin->role_id) {
                
                if ($targetAdmin->id === $currentUser?->id && $currentUserLevel !== 1) {
                    $validator->errors()->add('role_id', 'Lỗi bảo mật: Bạn không thể tự thay đổi chức vụ của chính mình.');
                }

                $targetRole = Role::find($newRoleId);
                if ($targetRole && $currentUserLevel >= $targetRole->level && $currentUserLevel !== 1) {
                    $validator->errors()->add('role_id', 'Lỗi phân quyền: Bạn không thể thăng cấp nhân sự này lên chức vụ ngang hoặc cao hơn quyền của mình.');
                }
            }
        });
    }
}