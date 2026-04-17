<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientReviewController extends Controller
{
    // Lấy TẤT CẢ sản phẩm kèm dữ liệu Đánh giá cũ (nếu có)
    public function getItemsForReview($orderId)
    {
        try {
            $order = Order::with('items')->where('user_id', Auth::id())->findOrFail($orderId);

            if ($order->status !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể đánh giá đơn hàng đã hoàn thành.'], 400);
            }

            $items = $order->items->map(function ($item) use ($orderId) {
                $itemData = $item->toArray();
                
                // Lấy dữ liệu đánh giá cũ nếu đã từng đánh giá
                $oldReview = Review::where('order_id', $orderId)
                                    ->where('product_id', $item->product_id)
                                    ->first();
                                    
                $itemData['is_reviewed'] = $oldReview ? true : false;
                $itemData['review_data'] = $oldReview; // Trả về frontend để hiện lại lên form
                return $itemData;
            });

            return response()->json(['success' => true, 'data' => $items]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }

    // Update hoặc Create Review mới
    public function store(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'reviews' => 'required|array',
                'reviews.*.product_id' => 'required|integer|exists:products,id',
                'reviews.*.rating' => 'required|integer|min:1|max:5',
            ]);

            $user = Auth::user();
            $order = Order::where('user_id', $user->id)->findOrFail($request->order_id);

            if ($order->status !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Đơn hàng chưa hoàn thành.'], 400);
            }

            $reviewsData = $request->allFiles()['reviews'] ?? []; 
            $inputs = $request->input('reviews', []);

            foreach ($inputs as $index => $itemReview) {
                $oldReview = Review::where('order_id', $order->id)->where('product_id', $itemReview['product_id'])->first();

                // Mặc định giữ lại ảnh cũ
                $imagePaths = $oldReview ? $oldReview->images : []; 

                // Nếu có upload ảnh mới thì GHI ĐÈ luôn ảnh cũ
                if (isset($reviewsData[$index]['images'])) {
                    $imagePaths = [];
                    foreach ($reviewsData[$index]['images'] as $image) {
                        $imagePaths[] = $image->store('reviews', 'public');
                    }
                }

                // Dùng updateOrCreate: Nếu có rồi thì Cập nhật, chưa có thì Tạo mới
                Review::updateOrCreate(
                    [
                        'order_id' => $order->id,
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