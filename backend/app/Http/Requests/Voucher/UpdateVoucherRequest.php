<?php

// File: backend/app/Http/Requests/Voucher/UpdateVoucherRequest.php

namespace App\Http\Requests\Voucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $voucherId = $this->route('voucher');

        return [
            'name'                 => ['required', 'string', 'max:255'],
            'code'                 => [
                'required',
                'string',
                'min:4',
                'max:30',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('vouchers', 'code')->ignore($voucherId)->whereNull('deleted_at')
            ],
            'discount_type'        => ['required', 'string', Rule::in(['fixed', 'percent'])],
            'discount_value'       => ['required', 'numeric', 'min:0'],
            'max_discount_amount'  => ['nullable', 'numeric', 'min:0'],
            'min_spend'            => ['required', 'numeric', 'min:0'],
            'apply_type'           => ['required', 'string', Rule::in(['all', 'specific_products', 'specific_categories'])],
            'conditions'           => ['nullable', 'array'],
            'usage_limit'          => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['required', 'integer', 'min:1'],
            'start_time'           => ['required', 'date'],
            'end_time'             => ['nullable', 'date', 'after:start_time'],
            'is_public'            => ['required', 'boolean'],
            'status'               => ['required', 'string', Rule::in(['active', 'hidden', 'expired'])],
        ];
    }
}
