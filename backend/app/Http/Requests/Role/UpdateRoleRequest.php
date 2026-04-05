<?php

// File: backend/app/Http/Requests/Role/UpdateRoleRequest.php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'value' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9\-\_]+$/',
                Rule::unique('roles', 'value')->ignore($roleId)->whereNull('deleted_at'),
            ],
            'label'       => ['required', 'string', 'max:70'],
            'badge_class' => ['nullable', 'string', 'max:50'],
            'level'       => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Mã vai trò không được để trống.',
            'value.string'   => 'Mã vai trò phải là chuỗi ký tự.',
            'value.max'      => 'Mã vai trò không được vượt quá 50 ký tự.',
            'value.regex'    => 'Mã vai trò không hợp lệ (chỉ dùng chữ thường, số, dấu - hoặc _).',
            'value.unique'   => 'Mã vai trò này đã bị trùng lặp với một vai trò khác.',
            
            'label.required' => 'Tên hiển thị không được để trống.',
            'label.string'   => 'Tên hiển thị phải là chuỗi ký tự.',
            'label.max'      => 'Tên hiển thị không được vượt quá 70 ký tự.',
            
            'badge_class.string' => 'Class màu sắc phải là chuỗi ký tự.',
            'badge_class.max'    => 'Class màu sắc không được vượt quá 50 ký tự.',
            
            'level.required' => 'Cấp độ quyền hạn không được để trống.',
            'level.integer'  => 'Cấp độ phải là một số nguyên.',
            'level.min'      => 'Cấp độ nhỏ nhất là 1 (Quyền cao nhất).',
            'level.max'      => 'Cấp độ lớn nhất là 10 (Quyền thấp nhất).',
        ];
    }
}