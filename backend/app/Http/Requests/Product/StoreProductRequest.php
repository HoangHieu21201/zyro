<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'min:3', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->whereNull('deleted_at')],
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'brand_id'          => ['nullable', 'integer', 'exists:brands,id'],
            'base_price'        => ['required', 'numeric', 'min:0', 'max:9999999999'], 
            'description'       => ['nullable', 'string', 'max:5000'],
            'care_instructions' => ['nullable', 'string', 'max:2000'],
            'gender'            => ['nullable', 'string', 'max:50', Rule::in(['Unisex', 'Men', 'Women', 'Kids'])],
            'fit_type'          => ['nullable', 'string', 'max:100'],
            'is_featured'       => ['nullable', 'in:true,false,1,0'],
            'status'            => ['required', 'string', Rule::in(['published', 'draft', 'hidden'])],
            
            'thumbnail_image'   => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], 
            'gallery_images'    => ['nullable', 'array', 'max:8'],
            'gallery_images.*'  => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            
            'variants_data'     => ['required', 'string'], 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Tên sản phẩm không được để trống.',
            'name.min'                 => 'Tên sản phẩm quá ngắn (Tối thiểu 3 ký tự).',
            'slug.unique'              => 'Đường dẫn (Slug) này đã tồn tại.',
            'category_id.required'     => 'Bắt buộc chọn Danh mục.',
            'base_price.required'      => 'Vui lòng nhập giá cơ sở tham khảo.',
            'base_price.max'           => 'Giá cơ sở vượt quá giới hạn hệ thống.',
            'thumbnail_image.required' => 'Ảnh đại diện sản phẩm là bắt buộc.',
            'gallery_images.max'       => 'Chỉ được phép tải lên tối đa 8 ảnh thư viện.',
            'variants_data.required'   => 'Lỗi dữ liệu: Cần ít nhất 1 biến thể.',
        ];
    }
}