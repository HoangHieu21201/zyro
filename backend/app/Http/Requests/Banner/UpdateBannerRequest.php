<?php

namespace App\Http\Requests\Banner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return true; 
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'min:3', 'max:255'],
            'image_desktop' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], 
            'image_mobile'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'target_url'    => ['nullable', 'string', 'max:500'],
            'position'      => ['required', 'string', Rule::in(['main_slider', 'home_banner_1', 'home_banner_2', 'popup'])],
            'start_time'    => ['nullable', 'date'],
            'end_time'      => ['nullable', 'date', 'after:start_time'],
            'status'        => ['required', 'string', Rule::in(['active', 'hidden'])],
            'remove_mobile' => ['nullable', 'in:true,false,1,0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'         => 'Vui lòng nhập tiêu đề Banner.',
            'title.min'              => 'Tiêu đề quá ngắn (Tối thiểu 3 ký tự).',
            'title.max'              => 'Tiêu đề quá dài (Tối đa 255 ký tự).',
            'image_desktop.image'    => 'File tải lên phải là hình ảnh.',
            'image_desktop.max'      => 'Dung lượng ảnh Desktop không được vượt quá 5MB.',
            'image_mobile.max'       => 'Dung lượng ảnh Mobile không được vượt quá 5MB.',
            'position.required'      => 'Vui lòng chọn vị trí hiển thị.',
            'position.in'            => 'Vị trí hiển thị không hợp lệ.',
            'end_time.after'         => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu.',
            'status.in'              => 'Trạng thái phát hành không hợp lệ.',
        ];
    }
}