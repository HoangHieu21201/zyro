<?php

// File: backend/app/Http/Requests/Brand/UpdateBrandRequest.php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ĐÃ BỔ SUNG min:3
            'name'        => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_logo' => ['nullable', 'in:true,false,1,0'],
            'status'      => ['required', 'string', Rule::in(['active', 'hidden'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Tên thương hiệu không được để trống.',
            'name.string'     => 'Tên thương hiệu phải là chuỗi ký tự.',
            'name.min'        => 'Tên thương hiệu quá ngắn (Tối thiểu 3 ký tự).',
            'name.max'        => 'Tên thương hiệu không được vượt quá 255 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'logo.image'      => 'File tải lên phải là hình ảnh.',
            'logo.mimes'      => 'Logo chỉ hỗ trợ định dạng: jpeg, png, jpg, webp.',
            'logo.max'        => 'Dung lượng Logo tối đa không được vượt quá 5MB.',
            'status.required' => 'Vui lòng chọn trạng thái hiển thị.',
            'status.in'       => 'Trạng thái thương hiệu không hợp lệ.',
        ];
    }
}