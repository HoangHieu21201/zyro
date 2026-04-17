<?php

// File: backend/app/Http/Requests/Lookbook/UpdateLookbookRequest.php

namespace App\Http\Requests\Lookbook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLookbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lookbookId = $this->route('lookbook');

        return [
            'name'                 => ['required', 'string', 'min:3', 'max:150'],
            'slug'                 => ['required', 'string', 'max:255', Rule::unique('lookbooks', 'slug')->ignore($lookbookId)->whereNull('deleted_at')],
            'description'          => ['nullable', 'string', 'max:2000'],
            'gender'               => ['nullable', 'string', 'max:50', Rule::in(['Unisex', 'Men', 'Women', 'Kids'])],
            'main_image'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'total_price_estimate' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'status'               => ['required', 'string', Rule::in(['published', 'draft', 'hidden'])],
            
            // Bổ sung rule cho trường mới
            'usage_limit'          => ['nullable', 'integer', 'min:0', 'max:10000'], 
            
            'items_data'           => ['required', 'json'], 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'Tên Lookbook không được để trống.',
            'name.min'                      => 'Tên Lookbook quá ngắn (Tối thiểu 3 ký tự).',
            'name.max'                      => 'Tên Lookbook quá dài (Tối đa 150 ký tự).',
            'slug.unique'                   => 'Đường dẫn (Slug) này đã tồn tại.',
            'description.max'               => 'Mô tả không được vượt quá 2000 ký tự.',
            'gender.in'                     => 'Giới tính không hợp lệ.',
            'main_image.image'              => 'File tải lên phải là hình ảnh (Tối đa 5MB).',
            'total_price_estimate.required' => 'Vui lòng nhập giá trị ước tính của set đồ.',
            'total_price_estimate.min'      => 'Giá trị ước tính không được là số âm.',
            'total_price_estimate.max'      => 'Giá trị ước tính vượt quá giới hạn hệ thống.',
            'status.in'                     => 'Trạng thái phát hành không hợp lệ.',
            
            // Bổ sung message lỗi cho trường mới
            'usage_limit.integer'           => 'Giới hạn số lượng phải là số nguyên.',
            'usage_limit.min'               => 'Giới hạn số lượng không được nhỏ hơn 0.',
            'usage_limit.max'               => 'Giới hạn số lượng không được vượt quá 10000.',
            
            'items_data.required'           => 'Lookbook phải có ít nhất 1 sản phẩm được ghim.',
            'items_data.json'               => 'Lỗi cấu trúc: Dữ liệu sản phẩm ghim không đúng chuẩn JSON.',
        ];
    }
}