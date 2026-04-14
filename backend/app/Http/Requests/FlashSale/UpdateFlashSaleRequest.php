<?php

namespace App\Http\Requests\FlashSale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('flash_sale');

        return [
            'name'         => ['required', 'string', 'min:3', 'max:255'],
            'slug'         => ['required', 'string', 'max:255', Rule::unique('flash_sales', 'slug')->ignore($id)],
            'banner_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_banner'=> ['nullable', 'in:true,false,1,0'],
            'start_time'   => ['required', 'date'],
            'end_time'     => ['required', 'date', 'after:start_time'],
            'status'       => ['required', 'string', Rule::in(['active', 'hidden', 'ended'])],
            'items_data'   => ['required', 'json'],
        ];
    }
}