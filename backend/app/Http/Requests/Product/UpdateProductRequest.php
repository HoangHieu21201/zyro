<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware đã lo phần quyền
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'name'              => ['required', 'string', 'min:3', 'max:255'],
            'slug'              => ['required', 'string', 'min:3', 'max:255', Rule::unique('products', 'slug')->ignore($productId)->whereNull('deleted_at')],
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'brand_id'          => ['nullable', 'integer', 'exists:brands,id'],
            'base_price'        => ['required', 'numeric', 'min:0', 'max:9999999999'], 
            'description'       => ['nullable', 'string', 'max:5000'],
            'care_instructions' => ['nullable', 'string', 'max:2000'],
            'gender'            => ['nullable', 'string', 'max:50', Rule::in(['Unisex', 'Men', 'Women', 'Kids'])],
            'fit_type'          => ['nullable', 'string', 'max:100'],
            'is_featured'       => ['nullable', 'in:true,false,1,0'],
            'status'            => ['required', 'string', Rule::in(['published', 'draft', 'hidden'])],
            
            'thumbnail_image'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], 
            'gallery_images'    => ['nullable', 'array', 'max:8'],
            'gallery_images.*'  => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            
            'variants_data'     => ['required', 'json'], 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Tên sản phẩm không được để trống.',
            'name.min'                 => 'Tên sản phẩm quá ngắn (Tối thiểu 3 ký tự).',
            'name.max'                 => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            
            'slug.required'            => 'Đường dẫn (Slug) không được để trống.',
            'slug.min'                 => 'Đường dẫn (Slug) quá ngắn.',
            'slug.unique'              => 'Đường dẫn (Slug) này đã tồn tại.',
            
            'category_id.required'     => 'Bắt buộc chọn Danh mục.',
            'category_id.exists'       => 'Danh mục không tồn tại hoặc đã bị xóa.',
            
            'brand_id.exists'          => 'Thương hiệu không tồn tại hoặc đã bị xóa.',
            
            'base_price.required'      => 'Vui lòng nhập giá cơ sở tham khảo.',
            'base_price.numeric'       => 'Giá cơ sở phải là một số hợp lệ.',
            'base_price.min'           => 'Giá cơ sở không được là số âm.',
            'base_price.max'           => 'Giá cơ sở vượt quá giới hạn hệ thống.',
            
            'description.max'          => 'Mô tả sản phẩm không được vượt quá 5000 ký tự.',
            'care_instructions.max'    => 'Hướng dẫn bảo quản không được vượt quá 2000 ký tự.',
            
            'thumbnail_image.image'    => 'File tải lên phải là định dạng hình ảnh.',
            'thumbnail_image.max'      => 'Ảnh đại diện không được vượt quá 5MB.',
            
            'gallery_images.max'       => 'Chỉ được phép tải lên tối đa 8 ảnh thư viện.',
            'gallery_images.*.max'     => 'Mỗi ảnh trong thư viện không được vượt quá 5MB.',
            
            'variants_data.required'   => 'Lỗi dữ liệu: Cần ít nhất 1 biến thể.',
            'variants_data.json'       => 'Lỗi cấu trúc: Dữ liệu biến thể không đúng chuẩn JSON.',
            
            'status.required'          => 'Vui lòng chọn trạng thái hiển thị.',
            'status.in'                => 'Trạng thái sản phẩm không hợp lệ.',
        ];
    }
}