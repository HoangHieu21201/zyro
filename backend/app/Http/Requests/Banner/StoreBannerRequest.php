<?php


namespace App\Http\Requests\Banner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'image_desktop' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Bắt buộc
            'image_mobile'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'target_url'    => ['nullable', 'string', 'max:500'],
            'position'      => ['required', 'string', 'max:50'], // VD: main_slider, home_banner_1
            'start_time'    => ['nullable', 'date'],
            'end_time'      => ['nullable', 'date', 'after:start_time'],
            'status'        => ['required', 'string', Rule::in(['active', 'hidden'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'         => 'Vui lòng nhập tiêu đề Banner.',
            'image_desktop.required' => 'Bắt buộc phải có ảnh cho giao diện Desktop.',
            'image_desktop.image'    => 'File tải lên phải là hình ảnh (Tối đa 5MB).',
            'end_time.after'         => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu.',
        ];
    }
}