<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Http\Requests\Review\ReplyReviewRequest;
use App\Events\ReviewEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    private string $cacheVersionKey = 'reviews_admin_cache_version';

    // Dọn cache phân trang bằng Versioning
    private function clearCache(): void
    {
        Cache::increment($this->cacheVersionKey);
    }

    // Danh sách đánh giá
    public function index(Request $request): JsonResponse
    {
        try {
            $version = Cache::rememberForever($this->cacheVersionKey, fn() => 1);
            $cacheKey = sprintf('reviews_admin_v%s_p%s_r%s_s%s',
                $version,
                $request->get('page', 1),
                $request->get('rating', 'all'),
                $request->get('status', 'all')
            );

            $reviews = Cache::remember($cacheKey, 86400, function() use ($request) {
                $query = Review::with(['user:id,full_name,email,avatar_url', 'product:id,name,thumbnail_image'])
                               ->withTrashed()
                               ->orderBy('id', 'desc');

                if ($request->has('rating') && $request->rating !== '') {
                    $query->where('rating', $request->rating);
                }
                if ($request->has('status') && $request->status !== '') {
                    $query->where('status', $request->status);
                }

                return $query->paginate(15);
            });
            
            return response()->json(['success' => true, 'data' => $reviews]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách đánh giá: ' . $e->getMessage()], 500);
        }
    }

    // Xem chi tiết
    public function show($id): JsonResponse
    {
        try {
            $review = Review::withTrashed()
                ->with(['user', 'product', 'order:id,order_code'])
                ->findOrFail($id);
                
            return response()->json(['success' => true, 'data' => $review]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đánh giá.'], 404);
        }
    }

    // Phản hồi
    public function reply(ReplyReviewRequest $request, $id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            $review->update(['admin_reply' => $request->validated()['admin_reply']]);

            $this->clearCache();

            $review->load(['user', 'product']);
            broadcast(new ReviewEvent('updated', $review))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã lưu phản hồi thành công!', 'data' => $review]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // Duyệt / Ẩn
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,hidden']);
        try {
            $review = Review::findOrFail($id);
            $review->update(['status' => $request->status]);

            $this->clearCache();

            broadcast(new ReviewEvent('updated', $review))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Đưa vào thùng rác
    public function destroy($id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            $review->delete();

            $this->clearCache();

            broadcast(new ReviewEvent('deleted', $review))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã xóa đánh giá này.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()], 500);
        }
    }

    // Khôi phục
    public function restore($id): JsonResponse
    {
        try {
            $review = Review::withTrashed()->findOrFail($id);
            $review->restore();

            $this->clearCache();

            broadcast(new ReviewEvent('restored', $review))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã khôi phục đánh giá.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi khôi phục: ' . $e->getMessage()], 500);
        }
    }
}