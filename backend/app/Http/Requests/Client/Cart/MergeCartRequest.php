<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class MergeCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'local_items' => 'present|array',
            'local_items.*.variant_id' => 'required|integer',
            'local_items.*.quantity' => 'required|integer|min:1|max:50',
        ];
    }
}