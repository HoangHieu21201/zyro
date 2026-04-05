<?php

// File: backend/app/Http/Requests/Category/UpdateCategoryRequest.php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'parent_id'         => ['nullable', 'integer', 'exists:categories,id'],
            'description'       => ['nullable', 'string'],
            'thumbnail'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'size_guide_image'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'attributes_schema' => ['nullable', 'array'],
            'status'            => ['required', 'string', Rule::in(['active', 'hidden'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Tên danh mục không được để trống.',
            'name.string'        => 'Tên danh mục phải là chuỗi ký tự.',
            'name.max'           => 'Tên danh mục không được vượt quá 255 ký tự.',
            
            'parent_id.integer'  => 'Danh mục cha không hợp lệ.',
            'parent_id.exists'   => 'Danh mục cha không tồn tại trong hệ thống.',
            
            'thumbnail.image'    => 'File tải lên phải là hình ảnh.',
            'thumbnail.mimes'    => 'Ảnh đại diện chỉ hỗ trợ định dạng: jpeg, png, jpg, webp.',
            'thumbnail.max'      => 'Dung lượng ảnh đại diện tối đa không được vượt quá 5MB.',
            
            'size_guide_image.image' => 'File tải lên phải là hình ảnh.',
            'size_guide_image.mimes' => 'Ảnh hướng dẫn chọn size chỉ hỗ trợ định dạng: jpeg, png, jpg, webp.',
            'size_guide_image.max'   => 'Dung lượng ảnh hướng dẫn chọn size tối đa không được vượt quá 5MB.',
            
            'sort_order.integer' => 'Thứ tự sắp xếp phải là số nguyên.',
            'sort_order.min'     => 'Thứ tự sắp xếp không được âm.',
            
            'attributes_schema.array' => 'Định dạng thuộc tính schema không hợp lệ.',
            
            'status.required'    => 'Vui lòng chọn trạng thái danh mục.',
            'status.in'          => 'Trạng thái danh mục không hợp lệ.',
        ];
    }
}