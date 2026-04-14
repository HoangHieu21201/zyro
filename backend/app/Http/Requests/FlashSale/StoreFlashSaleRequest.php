<?php

namespace App\Http\Requests\FlashSale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:3', 'max:255'],
            'slug'         => ['required', 'string', 'max:255', Rule::unique('flash_sales', 'slug')],
            'banner_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'start_time'   => ['required', 'date'],
            'end_time'     => ['required', 'date', 'after:start_time'],
            'status'       => ['required', 'string', Rule::in(['active', 'hidden', 'ended'])],
            
            // Ép JSON array chứa các biến thể tham gia Flash Sale
            'items_data'   => ['required', 'json'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Vui lòng nhập tên chương trình Flash Sale.',
            'slug.unique'         => 'Đường dẫn (Slug) này đã tồn tại.',
            'banner_image.image'  => 'Banner phải là định dạng hình ảnh.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'end_time.after'      => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
            'items_data.required' => 'Flash Sale phải có ít nhất 1 sản phẩm.',
            'items_data.json'     => 'Định dạng sản phẩm không hợp lệ.',
        ];
    }
}