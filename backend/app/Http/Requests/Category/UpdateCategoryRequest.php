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
        $categoryId = $this->route('category');

        return [
            'name'                => ['required', 'string', 'min:3', 'max:150'],
            'parent_id'           => [
                'nullable', 
                'integer', 
                Rule::exists('categories', 'id')->whereNull('deleted_at')->whereNot('id', $categoryId)
            ],
            'description'         => ['nullable', 'string', 'max:2000'],
            'thumbnail'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_thumbnail'    => ['nullable', 'in:true,false,1,0'],
            'size_guide_image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_size_guide'   => ['nullable', 'in:true,false,1,0'],
            'sort_order'          => ['nullable', 'integer', 'min:0', 'max:999999'],
            
            'attributes_schema'   => ['nullable', 'array', 'max:10'],
            'attributes_schema.*' => ['required', 'string', 'max:50'],
            
            'status'              => ['required', 'string', Rule::in(['active', 'hidden'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Tên danh mục không được để trống.',
            'name.min'                  => 'Tên danh mục quá ngắn (Tối thiểu 3 ký tự).',
            'name.string'               => 'Tên danh mục phải là chuỗi ký tự.',
            'name.max'                  => 'Tên danh mục không được vượt quá 150 ký tự.',
            
            'parent_id.integer'         => 'Danh mục cha không hợp lệ.',
            'parent_id.exists'          => 'Danh mục cha không tồn tại (Hoặc bạn đang cố chọn chính danh mục này làm cha của nó).',
            
            'description.max'           => 'Mô tả không được vượt quá 2000 ký tự.',
            
            'thumbnail.image'           => 'File tải lên phải là hình ảnh.',
            'thumbnail.mimes'           => 'Ảnh đại diện chỉ hỗ trợ định dạng: jpeg, png, jpg, webp.',
            'thumbnail.max'             => 'Dung lượng ảnh đại diện tối đa không được vượt quá 5MB.',
            
            'size_guide_image.image'    => 'File tải lên phải là hình ảnh.',
            'size_guide_image.mimes'    => 'Ảnh hướng dẫn chọn size chỉ hỗ trợ định dạng: jpeg, png, jpg, webp.',
            'size_guide_image.max'      => 'Dung lượng ảnh tối đa không được vượt quá 5MB.',
            
            'sort_order.integer'        => 'Thứ tự sắp xếp phải là số nguyên.',
            'sort_order.min'            => 'Thứ tự sắp xếp không được âm.',
            'sort_order.max'            => 'Thứ tự sắp xếp vượt quá giới hạn.',
            
            'attributes_schema.array'   => 'Định dạng thuộc tính không hợp lệ.',
            'attributes_schema.max'     => 'Chỉ được phép cấu hình tối đa 10 thuộc tính cho một danh mục.',
            'attributes_schema.*.max'   => 'Tên một thuộc tính không được dài quá 50 ký tự.',
            
            'status.required'           => 'Vui lòng chọn trạng thái danh mục.',
            'status.in'                 => 'Trạng thái danh mục không hợp lệ.',
        ];
    }
}