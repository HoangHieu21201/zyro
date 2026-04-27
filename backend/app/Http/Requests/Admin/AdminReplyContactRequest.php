<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminReplyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply_message' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reply_message.required' => 'Nội dung phản hồi không được để trống.',
            'reply_message.string'   => 'Nội dung phản hồi không hợp lệ.',
            'reply_message.min'      => 'Vui lòng nhập ít nhất 20 ký tự để thể hiện sự chuyên nghiệp với khách hàng.',
            'reply_message.max'      => 'Nội dung quá dài, vui lòng rút gọn dưới 5000 ký tự.',
        ];
    }
}