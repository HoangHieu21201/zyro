<?php

namespace App\Http\Requests\Tier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'min:3', 'max:100', Rule::unique('membership_tiers', 'name')],
            'min_spent'            => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'min_orders'           => ['required', 'integer', 'min:0', 'max:999999'],
            'discount_percent'     => ['required', 'numeric', 'min:0', 'max:100'],
            'yearly_service_quota' => ['required', 'integer', 'min:0', 'max:999999'],
            'icon'                 => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Tên hạng thành viên không được để trống.',
            'name.min'                  => 'Tên hạng quá ngắn (Tối thiểu 3 ký tự).',
            'name.unique'               => 'Tên hạng này đã tồn tại trong hệ thống.',
            'min_spent.min'             => 'Chi tiêu tối thiểu không được là số âm.',
            'min_spent.max'             => 'Chi tiêu tối thiểu vượt quá giới hạn hệ thống.',
            'discount_percent.max'      => 'Phần trăm giảm giá tối đa là 100%.',
            'icon.image'                => 'Icon phải là định dạng hình ảnh hợp lệ.',
        ];
    }
}