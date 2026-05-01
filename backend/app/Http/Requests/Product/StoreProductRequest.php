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
            'slug'              => ['required', 'string', 'min:3', 'max:255', Rule::unique('products', 'slug')->whereNull('deleted_at')],
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'brand_id'          => ['nullable', 'integer', 'exists:brands,id'],
            // ĐÃ FIX: Giá cơ sở tối thiểu phải là 1.000đ
            'base_price'        => ['required', 'numeric', 'min:1000', 'max:9999999999'], 
            'description'       => ['nullable', 'string', 'max:5000'],
            'care_instructions' => ['nullable', 'string', 'max:2000'],
            'gender'            => ['nullable', 'string', 'max:50', Rule::in(['Unisex', 'Men', 'Women', 'Kids'])],
            'fit_type'          => ['nullable', 'string', 'max:100'],
            'is_featured'       => ['nullable', 'in:true,false,1,0'],
            'status'            => ['required', 'string', Rule::in(['published', 'draft', 'hidden'])],
            
            'thumbnail_image'   => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], 
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
            'slug.unique'              => 'Đường dẫn (Slug) này đã tồn tại.',
            
            'category_id.required'     => 'Bắt buộc chọn Danh mục.',
            'category_id.exists'       => 'Danh mục không tồn tại hoặc đã bị xóa.',
            
            'brand_id.exists'          => 'Thương hiệu không tồn tại hoặc đã bị xóa.',
            
            'base_price.required'      => 'Vui lòng nhập giá cơ sở tham khảo.',
            'base_price.numeric'       => 'Giá cơ sở phải là một số.',
            'base_price.min'           => 'Giá cơ sở tối thiểu phải là 1.000đ.',
            'base_price.max'           => 'Giá cơ sở vượt quá giới hạn hệ thống.',
            
            'description.max'          => 'Mô tả sản phẩm không được vượt quá 5000 ký tự.',
            'care_instructions.max'    => 'Hướng dẫn bảo quản không được vượt quá 2000 ký tự.',
            
            'thumbnail_image.required' => 'Ảnh đại diện sản phẩm là bắt buộc.',
            'thumbnail_image.image'    => 'File tải lên phải là định dạng hình ảnh.',
            'thumbnail_image.max'      => 'Ảnh đại diện không được vượt quá 5MB.',
            
            'gallery_images.max'       => 'Chỉ được phép tải lên tối đa 8 ảnh thư viện.',
            'gallery_images.*.max'     => 'Mỗi ảnh trong thư viện không được vượt quá 5MB.',
            
            'variants_data.required'   => 'Lỗi dữ liệu: Cần ít nhất 1 biến thể.',
            'variants_data.json'       => 'Lỗi cấu trúc: Dữ liệu biến thể không đúng chuẩn JSON.',
        ];
    }

    /**
     * ĐÃ FIX: QUÉT X-QUANG VÀO TẬN BÊN TRONG CHUỖI JSON ĐỂ KIỂM TRA GIÁ TỪNG BIẾN THỂ
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('variants_data')) {
                $variants = json_decode($this->input('variants_data'), true);
                if (is_array($variants)) {
                    foreach ($variants as $index => $v) {
                        // 1. Chống bán giá 0đ
                        if (!isset($v['price']) || $v['price'] < 1000) {
                            $validator->errors()->add('variants_data', "Biến thể [{$v['sku']}] có giá bán ra không hợp lệ (Tối thiểu phải 1.000đ).");
                        }
                        // 2. Giá vốn không được âm
                        if (isset($v['cost_price']) && $v['cost_price'] < 0) {
                            $validator->errors()->add('variants_data', "Biến thể [{$v['sku']}] có giá nhập vốn không được là số âm.");
                        }
                        // 3. Khuyến mãi vô lý (Khuyến mãi đắt hơn cả giá gốc)
                        if (isset($v['promotional_price']) && $v['promotional_price'] > $v['price']) {
                            $validator->errors()->add('variants_data', "Biến thể [{$v['sku']}] có Giá khuyến mãi cao hơn cả Giá bán ra.");
                        }
                    }
                }
            }
        });
    }
}