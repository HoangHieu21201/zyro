<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Lấy dữ liệu Trang chủ (Sản phẩm mới, Nổi bật, Khuyến mãi...)
     */
    public function index(): JsonResponse
    {
        try {
            // Dùng Redis cache 10 phút. Nếu Admin đổi trạng thái SP, 
            // có thể viết thêm logic clear cache ở Admin ProductController sau.
            $homeData = Cache::remember('client_home_data', 600, function () {
                
                // 1. CHỈ LẤY SẢN PHẨM HỢP LỆ: Đang xuất bản (published) & Chưa bị xóa mềm
                $baseQuery = Product::where('status', 'published')
                    ->with(['variants.attributeValues.attribute']); // Load kèm biến thể để lấy Màu sắc

                // Lấy 12 Sản phẩm mới nhất
                $newArrivals = (clone $baseQuery)
                    ->orderBy('id', 'desc')
                    ->limit(12)
                    ->get()
                    ->map(fn($prod) => $this->formatForProductCard($prod));

                // Lấy 12 Sản phẩm Nổi bật (is_featured = true)
                $featuredProducts = (clone $baseQuery)
                    ->where('is_featured', true)
                    ->inRandomOrder() // Random để giao diện luôn tươi mới
                    ->limit(12)
                    ->get()
                    ->map(fn($prod) => $this->formatForProductCard($prod));

                return [
                    'new_arrivals' => $newArrivals,
                    'featured'     => $featuredProducts,
                ];
            });

            return response()->json(['success' => true, 'data' => $homeData]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải trang chủ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * HELPER: Uốn nắn dữ liệu Backend cho VỪA KHÍT với Props của ProductCard.vue
     * Nhờ hàm này, Frontend không cần phải code thêm bất kỳ logic tính toán nào!
     */
    private function formatForProductCard(Product $product): array
    {
        // 1. Xác định giá: Ưu tiên lấy giá nhỏ nhất từ các biến thể (hoặc giá base)
        $defaultVariant = $product->variants->where('is_default', true)->first() 
                          ?? $product->variants->first();

        $currentPrice = $defaultVariant ? ($defaultVariant->promotional_price ?? $defaultVariant->price) : 0;
        $oldPrice     = $defaultVariant && $defaultVariant->promotional_price ? $defaultVariant->price : null;

        // 2. Tính phần trăm giảm giá (Badge)
        $discountPercent = null;
        if ($oldPrice && $oldPrice > $currentPrice) {
            $discountPercent = round((($oldPrice - $currentPrice) / $oldPrice) * 100);
        }

        // 3. Trích xuất danh sách Màu sắc (Dành cho Swatches)
        $colors = [];
        foreach ($product->variants as $variant) {
            foreach ($variant->attributeValues as $attrVal) {
                // Giả sử attribute có tên là 'Màu sắc' hoặc 'Color'
                if (stripos($attrVal->attribute->name, 'màu') !== false || stripos($attrVal->attribute->name, 'color') !== false) {
                    $colors[$attrVal->value] = [
                        'name' => $attrVal->value,
                        'hex'  => $attrVal->meta_value ?? '#cccccc' // meta_value chứa mã mã màu Hex (VD: #FF0000)
                    ];
                }
            }
        }

        // Return array đúng chuẩn object mà Vue Component đang chờ đón
        return [
            'id'               => $product->id,
            'name'             => $product->name,
            'slug'             => $product->slug,
            'price'            => $currentPrice,
            'old_price'        => $oldPrice,
            'image'            => $product->thumbnail_image ? asset('storage/' . $product->thumbnail_image) : null,
            'discount_percent' => $discountPercent,
            'colors'           => array_values($colors) // Reset key array
        ];
    }
}