<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Lúc update số lượng thì variant_id không bắt buộc, chỉ check quantity
            'quantity' => 'required|integer|min:1|max:50',
        ];
    }
}