<?php

// File: backend/app/Http/Requests/Voucher/StoreVoucherRequest.php

namespace App\Http\Requests\Voucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'code'                 => [
                'required', 'string', 'min:4', 'max:30', 
                'regex:/^[A-Z0-9]+$/', // Mã chỉ gồm chữ IN HOA và số
                Rule::unique('vouchers', 'code')->whereNull('deleted_at')
            ],
            'discount_type'        => ['required', 'string', Rule::in(['fixed', 'percent'])],
            'discount_value'       => ['required', 'numeric', 'min:0'],
            'max_discount_amount'  => ['nullable', 'numeric', 'min:0'],
            'min_spend'            => ['required', 'numeric', 'min:0'],
            'apply_type'           => ['required', 'string', Rule::in(['all', 'specific_products', 'specific_categories'])],
            'conditions'           => ['nullable', 'array'],
            'usage_limit'          => ['nullable', 'integer', 'min:1'], // null = không giới hạn
            'usage_limit_per_user' => ['required', 'integer', 'min:1'],
            'start_time'           => ['required', 'date'],
            'end_time'             => ['nullable', 'date', 'after:start_time'],
            'is_public'            => ['required', 'boolean'],
            'status'               => ['required', 'string', Rule::in(['active', 'hidden', 'expired'])],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'      => 'Vui lòng nhập Mã Voucher.',
            'code.regex'         => 'Mã Voucher chỉ được chứa CHỮ IN HOA và SỐ viết liền (VD: ZYRO100K).',
            'code.unique'        => 'Mã Voucher này đã tồn tại trong hệ thống.',
            'end_time.after'     => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu.',
            'discount_type.in'   => 'Loại giảm giá không hợp lệ.',
        ];
    }
}