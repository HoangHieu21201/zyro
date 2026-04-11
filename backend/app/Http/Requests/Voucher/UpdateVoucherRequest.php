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
            // ĐÃ THÊM min:3
            'name'                 => ['required', 'string', 'min:3', 'max:255'],
            'code'                 => [
                'required', 'string', 'min:4', 'max:30', 
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('vouchers', 'code')->ignore($voucherId)->whereNull('deleted_at')
            ],
            'discount_type'        => ['required', 'string', Rule::in(['fixed', 'percent'])],
            // ĐÃ THÊM MAX ĐỂ CHỐNG TRÀN SỐ DỮ LIỆU CỦA DATABASE
            'discount_value'       => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'max_discount_amount'  => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'min_spend'            => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'apply_type'           => ['required', 'string', Rule::in(['all', 'specific_products', 'specific_categories'])],
            'conditions'           => ['nullable', 'array'],
            'usage_limit'          => ['nullable', 'integer', 'min:1', 'max:999999'],
            'usage_limit_per_user' => ['required', 'integer', 'min:1', 'max:999999'],
            'start_time'           => ['required', 'date'],
            'end_time'             => ['nullable', 'date', 'after:start_time'],
            'is_public'            => ['required', 'boolean'],
            'status'               => ['required', 'string', Rule::in(['active', 'hidden', 'expired'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Vui lòng nhập tên chiến dịch khuyến mãi.',
            'name.min'           => 'Tên chiến dịch quá ngắn (Tối thiểu 3 ký tự).',
            'code.required'      => 'Vui lòng nhập Mã Voucher.',
            'code.regex'         => 'Mã Voucher chỉ được chứa CHỮ IN HOA và SỐ viết liền.',
            'code.unique'        => 'Mã Voucher này đã tồn tại trong hệ thống.',
            'discount_value.max' => 'Mức giảm giá vượt quá giới hạn hệ thống.',
            'end_time.after'     => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu.',
            'discount_type.in'   => 'Loại giảm giá không hợp lệ.',
        ];
    }
}