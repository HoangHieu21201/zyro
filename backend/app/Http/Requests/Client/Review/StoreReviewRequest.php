<?php

namespace App\Http\Requests\Client\Review;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'                  => ['required', 'integer', 'exists:orders,id'],
            'reviews'                   => ['required', 'array', 'min:1'],
            'reviews.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'reviews.*.rating'          => ['required', 'integer', 'min:1', 'max:5'],
            'reviews.*.comment'         => ['nullable', 'string', 'max:1000'],
            'reviews.*.variant_name'    => ['nullable', 'string', 'max:255'],
            'reviews.*.fit_feedback'    => ['nullable', 'string', 'max:100'],
            'reviews.*.reviewer_height' => ['nullable', 'integer', 'min:50', 'max:250'],
            'reviews.*.reviewer_weight' => ['nullable', 'numeric', 'min:10', 'max:200'],
            
            // Bổ sung: chặn ảnh quá nặng hoặc file không phải là ảnh
            'reviews.*.images'          => ['nullable', 'array', 'max:5'],
            'reviews.*.images.*'        => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], 
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required'            => 'Thiếu thông tin đơn hàng.',
            'reviews.required'             => 'Không có dữ liệu đánh giá.',
            'reviews.min'                  => 'Cần ít nhất 1 đánh giá.',
            'reviews.*.rating.required'    => 'Vui lòng chọn số sao đánh giá.',
            'reviews.*.images.*.image'     => 'File tải lên phải là hình ảnh.',
            'reviews.*.images.*.max'       => 'Ảnh tải lên không được vượt quá 5MB.',
            'reviews.*.images.max'         => 'Chỉ được tải lên tối đa 5 ảnh cho mỗi đánh giá.',
        ];
    }

    /**
     * BỨC TƯỜNG LỬA CHẶN "FAKE REVIEW INJECTION"
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $orderId = $this->input('order_id');
            $userId = $this->user()->id ?? null;
            
            if (!$orderId || !$userId) return;

            // Truy vấn lấy đơn hàng KÈM danh sách các sản phẩm thực tế bên trong
            $order = Order::with('items')->where('user_id', $userId)->find($orderId);

            if (!$order) {
                $validator->errors()->add('order_id', 'Đơn hàng không tồn tại hoặc không thuộc quyền sở hữu của bạn.');
                return;
            }

            if ($order->status !== 'completed') {
                $validator->errors()->add('order_id', 'Lỗi: Bạn chỉ có thể đánh giá các đơn hàng đã giao thành công.');
                return;
            }

            // Lấy ra danh sách các Product ID hợp lệ mà khách đã thực sự mua
            $validProductIds = $order->items->pluck('product_id')->toArray();
            
            $submittedReviews = $this->input('reviews', []);
            $seenProducts = [];

            foreach ($submittedReviews as $index => $review) {
                $productId = $review['product_id'] ?? null;
                if (!$productId) continue;

                // 1. CHỐNG REVIEW ẢO: Sản phẩm này khách không hề mua trong đơn hàng này!
                if (!in_array($productId, $validProductIds)) {
                    $validator->errors()->add("reviews.{$index}.product_id", "Lỗi bảo mật: Sản phẩm không nằm trong danh sách mua hàng của đơn này.");
                }

                // 2. CHỐNG SPAM: Ngăn chặn gửi 100 cái review cho cùng 1 sản phẩm trong 1 lần gửi
                if (in_array($productId, $seenProducts)) {
                    $validator->errors()->add("reviews.{$index}.product_id", "Bạn không thể gửi đánh giá trùng lặp nhiều lần cho cùng một sản phẩm.");
                }
                $seenProducts[] = $productId;
            }
        });
    }
}