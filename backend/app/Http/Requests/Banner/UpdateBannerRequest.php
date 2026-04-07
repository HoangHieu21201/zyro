<?php


namespace App\Http\Requests\Banner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'image_desktop' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_mobile'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'target_url'    => ['nullable', 'string', 'max:500'],
            'position'      => ['required', 'string', 'max:50'],
            'start_time'    => ['nullable', 'date'],
            'end_time'      => ['nullable', 'date', 'after:start_time'],
            'status'        => ['required', 'string', Rule::in(['active', 'hidden'])],
            'remove_mobile' => ['nullable', 'in:true,false,1,0'],
        ];
    }
}