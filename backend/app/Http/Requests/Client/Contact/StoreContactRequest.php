<?php

namespace App\Http\Requests\Client\Contact;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NotDisposableEmail; // ĐÃ THÊM: Import Rule kiểm tra email rác

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'website_url'          => ['nullable', 'string', 'max:255'],
            'name'                 => ['required', 'string', 'min:2', 'max:100'],
            'phone'                => ['nullable', 'string', 'regex:/^(0[3|5|7|8|9])+([0-9]{8})$/'],
            
            // ĐÃ THÊM: Bổ sung "new NotDisposableEmail()" vào danh sách rule bảo vệ
            'email'                => ['required', 'email:rfc,dns', new NotDisposableEmail()],
            
            'subject'              => ['required', 'string', 'min:5', 'max:150'],
            'message'              => ['required', 'string', 'min:10', 'max:2000'],
            'g-recaptcha-response' => ['required', 'string'], // YÊU CẦU CAPTCHA CỦA BẠN
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'Vui lòng nhập họ và tên.',
            'name.min'                      => 'Họ và tên tối thiểu 2 ký tự.',
            'name.max'                      => 'Họ và tên tối đa 100 ký tự.',
            'phone.regex'                   => 'Số điện thoại không đúng định dạng.',
            'email.required'                => 'Vui lòng nhập địa chỉ email.',
            'email.email'                   => 'Địa chỉ email không hợp lệ hoặc domain không tồn tại.',
            'subject.required'              => 'Vui lòng nhập chủ đề liên hệ.',
            'subject.min'                   => 'Chủ đề tối thiểu 5 ký tự.',
            'subject.max'                   => 'Chủ đề tối đa 150 ký tự.',
            'message.required'              => 'Vui lòng nhập nội dung.',
            'message.min'                   => 'Nội dung tối thiểu 10 ký tự.',
            'message.max'                   => 'Nội dung tối đa 2000 ký tự.',
            'g-recaptcha-response.required' => 'Vui lòng xác nhận bạn không phải là Robot.',
        ];
    }
}