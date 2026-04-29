<?php

namespace App\Http\Requests\Client\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'    => ['required', 'string', 'max:150'],
            'customer_phone'   => ['required', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
            'customer_email'   => ['required', 'email', 'max:150'],
            'customer_address' => ['required', 'string', 'max:500'],
            
            'user_address_id'  => ['nullable', 'integer', 'exists:user_addresses,id'],
            'order_note'       => ['nullable', 'string', 'max:1000'],
            'payment_method'   => ['required', 'in:cod,momo,vnpay'],
            'coupon_code'      => ['nullable', 'string', 'exists:vouchers,code'],
            
            'require_vat'      => ['nullable', 'boolean'],
            'vat_info'         => ['nullable', 'array'],
            'vat_info.company_name' => ['required_if:require_vat,true', 'string', 'max:255'],
            'vat_info.tax_code'     => ['required_if:require_vat,true', 'string', 'max:50'],
            'vat_info.address'      => ['required_if:require_vat,true', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required'  => 'Vui lòng nhập họ và tên người nhận.',
            'customer_phone.regex'    => 'Số điện thoại không đúng định dạng.',
            'customer_email.email'    => 'Email không đúng định dạng.',
            'customer_address.required'=> 'Vui lòng nhập địa chỉ nhận hàng.',
            'payment_method.in'       => 'Phương thức thanh toán không hợp lệ.',
            'coupon_code.exists'      => 'Mã giảm giá không tồn tại trên hệ thống.',
            'vat_info.company_name.required_if' => 'Vui lòng nhập tên công ty để xuất VAT.',
            'vat_info.tax_code.required_if'     => 'Vui lòng nhập mã số thuế để xuất VAT.',
        ];
    }
}