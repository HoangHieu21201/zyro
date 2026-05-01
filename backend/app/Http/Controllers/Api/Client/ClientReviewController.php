<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Http\Requests\Client\Review\StoreReviewRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientReviewController extends Controller
{
    // lấy tất cả sản phẩm kèm dữ liệu đánh giá cũ (nếu có)
    public function getItemsForReview($orderId)
    {
        try {
            $order = Order::with('items')->where('user_id', Auth::id())->findOrFail($orderId);

            if ($order->status !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể đánh giá đơn hàng đã hoàn thành.'], 400);
            }

            $items = $order->items->map(function ($item) use ($orderId) {
                $itemData = $item->toArray();
                
                // lấy dữ liệu đánh giá cũ nếu đã từng đánh giá
                $oldReview = Review::where('order_id', $orderId)
                                    ->where('product_id', $item->product_id)
                                    ->first();
                                    
                $itemData['is_reviewed'] = $oldReview ? true : false;
                $itemData['review_data'] = $oldReview; 
                return $itemData;
            });

            return response()->json(['success' => true, 'data' => $items]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }

    // tạo mới hoặc cập nhật review (ĐÃ ĐƯỢC BẢO VỆ BỞI StoreReviewRequest)
    public function store(StoreReviewRequest $request)
    {
        try {
            // Toàn bộ validate, check mua hàng thật, check quyền đều đã được StoreReviewRequest xử lý tự động
            $user = Auth::user();
            $orderId = $request->input('order_id');
            $inputs = $request->input('reviews', []);

            foreach ($inputs as $index => $itemReview) {
                $oldReview = Review::where('order_id', $orderId)->where('product_id', $itemReview['product_id'])->first();

                // mặc định giữ lại ảnh cũ
                $imagePaths = $oldReview ? $oldReview->images : []; 

                // lấy file an toàn
                $uploadedImages = $request->file("reviews.{$index}.images");

                // nếu có upload ảnh mới thì ghi đè
                if ($uploadedImages) {
                    $imagePaths = [];
                    $imagesArray = is_array($uploadedImages) ? $uploadedImages : [$uploadedImages];
                    
                    foreach ($imagesArray as $image) {
                        /** @var \Illuminate\Http\UploadedFile $image */
                        $imagePaths[] = $image->store('reviews', 'public');
                    }
                }

                // Lưu xuống DB (Dữ liệu đã sạch 100%)
                Review::updateOrCreate(
                    [
                        'order_id' => $orderId,
                        'product_id' => $itemReview['product_id'],
                        'user_id' => $user->id,
                    ],
                    [
                        'variant_name'    => $itemReview['variant_name'] ?? 'Mặc định',
                        'rating'          => $itemReview['rating'],
                        'comment'         => $itemReview['comment'] ?? '',
                        'images'          => $imagePaths,
                        'fit_feedback'    => $itemReview['fit_feedback'] ?? null,
                        'reviewer_height' => $itemReview['reviewer_height'] ?? $user->height_cm ?? null,
                        'reviewer_weight' => $itemReview['reviewer_weight'] ?? $user->weight_kg ?? null,
                        'status'          => 'pending', 
                    ]
                );
            }

            return response()->json(['success' => true, 'message' => 'Đánh giá của bạn đã được lưu thành công!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý: ' . $e->getMessage()], 500);
        }
    }
}