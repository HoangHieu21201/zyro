<?php

// File: backend/app/Http/Requests/Lookbook/StoreLookbookRequest.php

namespace App\Http\Requests\Lookbook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLookbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'slug'                 => ['required', 'string', 'max:255', Rule::unique('lookbooks', 'slug')->whereNull('deleted_at')],
            'description'          => ['nullable', 'string', 'max:2000'],
            'gender'               => ['nullable', 'string', 'max:50', Rule::in(['Unisex', 'Men', 'Women', 'Kids'])], // Validate Giới tính
            'main_image'           => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'total_price_estimate' => ['required', 'numeric', 'min:0'],
            'status'               => ['required', 'string', Rule::in(['published', 'draft', 'hidden'])],
            'items_data'           => ['required', 'string'], 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'Tên Lookbook không được để trống.',
            'slug.unique'                   => 'Đường dẫn (Slug) này đã tồn tại.',
            'main_image.required'           => 'Ảnh Lookbook (Ảnh người mẫu) là bắt buộc.',
            'main_image.image'              => 'File tải lên phải là hình ảnh.',
            'total_price_estimate.required' => 'Vui lòng nhập giá trị ước tính của set đồ.',
            'items_data.required'           => 'Lookbook phải có ít nhất 1 sản phẩm được ghim.',
        ];
    }
}