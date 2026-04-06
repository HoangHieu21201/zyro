<?php

namespace App\Http\Requests\Tier;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:100', 'unique:membership_tiers,name'],
            'min_spent'            => ['required', 'numeric', 'min:0'],
            'min_orders'           => ['required', 'integer', 'min:0'],
            'discount_percent'     => ['required', 'numeric', 'min:0', 'max:100'],
            'yearly_service_quota' => ['required', 'integer', 'min:0'],
            'icon'                 => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Tên hạng thành viên không được để trống.',
            'name.unique'               => 'Tên hạng này đã tồn tại.',
            'min_spent.min'             => 'Chi tiêu tối thiểu không được là số âm.',
            'discount_percent.max'      => 'Phần trăm giảm giá tối đa là 100%.',
            'icon.image'                => 'Icon phải là định dạng hình ảnh.',
        ];
    }
}