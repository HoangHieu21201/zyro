<?php

namespace App\Http\Requests\Tier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tierId = $this->route('tier');

        return [
            'name'                 => ['required', 'string', 'max:100', Rule::unique('membership_tiers', 'name')->ignore($tierId)],
            'min_spent'            => ['required', 'numeric', 'min:0'],
            'min_orders'           => ['required', 'integer', 'min:0'],
            'discount_percent'     => ['required', 'numeric', 'min:0', 'max:100'],
            'yearly_service_quota' => ['required', 'integer', 'min:0'],
            'icon'                 => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'remove_icon'          => ['nullable', 'in:true,false,1,0'],
        ];
    }
}