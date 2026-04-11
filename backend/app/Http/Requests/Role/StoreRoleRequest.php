<?php

// File: backend/app/Http/Requests/Role/StoreRoleRequest.php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => [
                'required',
                'string',
                'min:3', // Chặn tạo code "a", "b"
                'max:50',
                'regex:/^[a-z0-9\-\_]+$/', // Chỉ cho phép chữ thường, số, gạch ngang, gạch dưới
                Rule::unique('roles', 'value')->whereNull('deleted_at'),
            ],
            'label'       => ['required', 'string', 'min:3', 'max:70'], // Tên hiển thị
            'badge_class' => ['nullable', 'string', 'max:50'],
            'level'       => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Mã vai trò không được để trống.',
            'value.min'      => 'Mã vai trò tối thiểu phải có 3 ký tự.',
            'value.string'   => 'Mã vai trò phải là chuỗi ký tự.',
            'value.max'      => 'Mã vai trò không được vượt quá 50 ký tự.',
            'value.regex'    => 'Mã vai trò không hợp lệ (chỉ dùng chữ thường, số, dấu - hoặc _).',
            'value.unique'   => 'Mã vai trò này đã tồn tại trong hệ thống.',
            
            'label.required' => 'Tên hiển thị không được để trống.',
            'label.min'      => 'Tên hiển thị quá ngắn (Tối thiểu 3 ký tự).',
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