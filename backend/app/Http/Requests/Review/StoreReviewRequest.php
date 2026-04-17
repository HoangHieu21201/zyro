<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
            'reviews' => 'required|array',
            'reviews.*.product_id' => 'required|integer|exists:products,id',
            'reviews.*.variant_name' => 'nullable|string',
            'reviews.*.rating' => 'required|integer|min:1|max:5',
            'reviews.*.comment' => 'nullable|string|max:1000',
            'reviews.*.fit_feedback' => 'nullable|string|in:Chật,Vừa vặn,Rộng',
            'reviews.*.reviewer_height' => 'nullable|integer|min:50|max:250',
            'reviews.*.reviewer_weight' => 'nullable|numeric|min:10|max:200',
            'reviews.*.images' => 'nullable|array|max:5',
            'reviews.*.images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}