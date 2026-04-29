<?php

namespace App\Http\Requests\Voucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Voucher;

class UpdateVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $voucherId = $this->route('voucher') ?? $this->route('id');

        return [
            'name'                 => ['required', 'string', 'min:3', 'max:255'],
            'code'                 => [
                'required', 'string', 'min:4', 'max:30', 
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('vouchers', 'code')->ignore($voucherId)->whereNull('deleted_at')
            ],
            'discount_type'        => ['required', 'string', Rule::in(['fixed', 'percent'])],
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $newLimit = $this->input('usage_limit');
            
            if (empty($newLimit)) return;

            $voucherId = $this->route('voucher') ?? $this->route('id');
            $voucher = Voucher::find($voucherId);

            if ($voucher) {
                $currentUsage = $voucher->usage_count ?? 0;

                if ($newLimit < $currentUsage) {
                    $validator->errors()->add(
                        'usage_limit', 
                        "Lỗi nghiệp vụ: Mã giảm giá này đã được sử dụng {$currentUsage} lần. Không thể đặt giới hạn mới ({$newLimit}) nhỏ hơn số lượt đã dùng thực tế!"
                    );
                }
            }
        });
    }
}