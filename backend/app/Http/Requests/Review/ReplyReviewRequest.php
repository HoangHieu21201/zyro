<?php

// File: backend/app/Http/Requests/Review/ReplyReviewRequest.php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class ReplyReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ÉP ADMIN REP CÓ TÂM: Tối thiểu 5 ký tự
            'admin_reply' => ['nullable', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_reply.string' => 'Nội dung phản hồi phải là văn bản.',
            'admin_reply.min'    => 'Phản hồi quá ngắn, vui lòng nhập rõ ràng hơn (Tối thiểu 5 ký tự).',
            'admin_reply.max'    => 'Nội dung phản hồi không được vượt quá 1000 ký tự.',
        ];
    }
}