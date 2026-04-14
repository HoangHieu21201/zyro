<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Lookbook;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $homeData = Cache::remember('client_home_data_dev', 5, function () {

                // 0. HERO BANNERS (Lấy ảnh từ vị trí main_slider)
                $heroBanners = Banner::where('status', 'active')
                    ->where('position', 'main_slider')
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn($b) => [
                        'id' => $b->id,
                        'title' => $b->title,
                        'image_desktop' => $this->getImageUrl($b->image_desktop),
                        'image_mobile' => $this->getImageUrl($b->image_mobile),
                        'target_url' => $b->target_url
                    ]);

                // 1. FLASH SALE
                $flashSale = FlashSale::where('status', 'active')
                    ->where('end_time', '>', now())
                    ->orderBy('start_time', 'asc')
                    ->with(['items.variant.product.variants.attributeValues.attribute'])
                    ->first();

                $fsData = null;
                if ($flashSale) {
                    $fsProducts = [];
                    foreach ($flashSale->items as $item) {
                        if (!$item->variant || !$item->variant->product || $item->variant->product->status !== 'published') continue;

                        $prodId = $item->variant->product_id;

                        if (!isset($fsProducts[$prodId]) || $item->flash_sale_price < $fsProducts[$prodId]['price']) {
                            $product = $item->variant->product;
                            $oldPrice = $item->variant->price;
                            $fsPrice = $item->flash_sale_price;
                            $discountPercent = ($oldPrice > 0 && $oldPrice > $fsPrice) ? round((($oldPrice - $fsPrice) / $oldPrice) * 100) : 0;

                            $formattedProd = $this->formatProduct($product);
                            $formattedProd['price'] = $fsPrice;
                            $formattedProd['old_price'] = $oldPrice;
                            $formattedProd['discount_percent'] = $discountPercent;
                            $formattedProd['image'] = $this->getImageUrl($item->variant->image_url ?? $product->thumbnail_image);

                            $fsProducts[$prodId] = $formattedProd;
                        }
                    }

                    $fsData = [
                        'id'           => $flashSale->id,
                        'name'         => $flashSale->name,
                        'banner_image' => $this->getImageUrl($flashSale->banner_image),
                        'start_time'   => $flashSale->start_time,
                        'end_time'     => $flashSale->end_time,
                        'products'     => array_slice(array_values($fsProducts), 0, 8)
                    ];
                }

                $baseQuery = Product::where('status', 'published')->with(['variants.attributeValues.attribute']);

                // 2. HÀNG MỚI VỀ
                $parentCategories = Category::whereNull('parent_id')->where('status', 'active')->orderBy('sort_order')->get(['id', 'name', 'slug']);
                $newArrivalsAll = (clone $baseQuery)->orderBy('id', 'desc')->limit(8)->get()->map(fn($p) => $this->formatProduct($p));

                // 3. ĐƯỢC YÊU THÍCH NHẤT (CÓ BANNER GIỮA TRANG 1)
                $hotTrends = (clone $baseQuery)->orderBy('view_count', 'desc')->orderBy('id', 'desc')->limit(8)->get()->map(fn($p) => $this->formatProduct($p));
                $bestSellers = (clone $baseQuery)->orderBy('sales_count', 'desc')->limit(8)->get()->map(fn($p) => $this->formatProduct($p));

                $trendingBannerRecord = Banner::where('status', 'active')
                    ->where('position', 'home_banner_1') 
                    ->orderBy('sort_order')
                    ->first();

                $trendingBanner = $trendingBannerRecord ? [
                    'title' => $trendingBannerRecord->title,
                    'image' => $this->getImageUrl($trendingBannerRecord->image_desktop),
                    'url'   => $trendingBannerRecord->target_url
                ] : null;

                // 4. DÀNH CHO BÉ
                $kidsCategory = Category::where('slug', 'tre-em')->orWhere('name', 'Trẻ Em')->first();
                $kidsProducts = collect([]);
                $kidsCatData = null;

                if ($kidsCategory) {
                    $kidsCatData = [
                        'name' => $kidsCategory->name,
                        'slug' => $kidsCategory->slug,
                        'thumbnail' => $this->getImageUrl($kidsCategory->thumbnail)
                    ];

                    $catIds = Category::where('parent_id', $kidsCategory->id)->pluck('id')->push($kidsCategory->id)->toArray();
                    $kidsProducts = (clone $baseQuery)
                        ->whereIn('category_id', $catIds)
                        ->orderBy('id', 'desc')
                        ->limit(8)
                        ->get()
                        ->map(fn($p) => $this->formatProduct($p));
                }

                // 5. BỘ SƯU TẬP (LOOKBOOK)
                $lookbooks = Lookbook::where('status', 'published')
                    ->with(['items.product.variants.attributeValues.attribute'])
                    ->orderBy('id', 'desc')
                    ->limit(4)
                    ->get()
                    ->map(function ($lb) {
                        $lbProducts = $lb->items->map(function ($item) {
                            if ($item->product && $item->product->status === 'published') {
                                return $this->formatProduct($item->product);
                            }
                            return null;
                        })->filter()->values();

                        return [
                            'id' => $lb->id,
                            'name' => $lb->name,
                            'slug' => $lb->slug,
                            'main_image' => $this->getImageUrl($lb->main_image),
                            'price_estimate' => $lb->total_price_estimate,
                            'products' => $lbProducts
                        ];
                    });

                return [
                    'hero_banners' => $heroBanners,
                    'flash_sale' => $fsData,
                    'new_arrivals' => [
                        'tabs'     => $parentCategories,
                        'products' => $newArrivalsAll
                    ],
                    'most_loved' => [
                        'hot_trends'   => $hotTrends,
                        'best_sellers' => $bestSellers,
                        'banner'       => $trendingBanner
                    ],
                    'kids' => [
                        'category' => $kidsCatData,
                        'products' => $kidsProducts
                    ],
                    'lookbooks' => $lookbooks
                ];
            });

            return response()->json(['success' => true, 'data' => $homeData]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải trang chủ: ' . $e->getMessage()], 500);
        }
    }

    private function getImageUrl(?string $path): ?string
    {
        if (!$path) return null;

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return 'http://127.0.0.1:8000/storage/' . ltrim($path, '/');
    }

    // ĐÃ THÊM: TỪ ĐIỂN MÀU SẮC TIẾNG VIỆT TỰ ĐỘNG
    private function getVietnameseColorHex(string $colorName): string
    {
        $map = [
            'đỏ đô' => '#991b1b', 'đỏ' => '#ef4444',
            'xanh navy' => '#1e3a8a', 'xanh biển' => '#3b82f6', 'xanh dương' => '#3b82f6', 'xanh lợt' => '#93c5fd',
            'xanh rêu' => '#4d7c0f', 'xanh lá' => '#22c55e', 'xanh ngọc' => '#14b8a6', 'xanh' => '#3b82f6',
            'đen' => '#171717', 'trắng' => '#ffffff',
            'vàng' => '#eab308', 'hồng phấn' => '#fbcfe8', 'hồng' => '#f472b6',
            'tím' => '#a855f7', 'cam' => '#f97316',
            'xám' => '#9ca3af', 'ghi' => '#9ca3af', 'tiêu' => '#d1d5db',
            'nâu' => '#78350f', 'be' => '#f5f5dc', 'kem' => '#fef3c7',
        ];

        $lower = mb_strtolower($colorName, 'UTF-8');
        foreach ($map as $key => $hex) {
            if (str_contains($lower, $key)) {
                return $hex;
            }
        }

        // CỨU CÁNH: Nếu màu lạ (chưa có trong từ điển) và cũng không được khai báo mã HEX trong Admin
        // Hệ thống sẽ dùng hàm băm (MD5) để tạo ra mã màu HEX ngẫu nhiên nhưng LUÔN CỐ ĐỊNH với chữ đó.
        return '#' . substr(md5($colorName), 0, 6);
    }

    // ĐÃ CẬP NHẬT: GOM NHÓM SIZE & TỰ ĐỘNG TÌM ẢNH ĐÚNG MÀU
    private function formatProduct(Product $product): array
    {
        $defaultVariant = $product->variants->where('is_default', true)->first() ?? $product->variants->first();
        $currentPrice = $defaultVariant ? ($defaultVariant->promotional_price ?? $defaultVariant->price) : $product->base_price;
        $oldPrice     = $defaultVariant && $defaultVariant->promotional_price ? $defaultVariant->price : null;

        $discountPercent = ($oldPrice && $oldPrice > $currentPrice) ? round((($oldPrice - $currentPrice) / $oldPrice) * 100) : null;

        $colors = [];
        foreach ($product->variants as $variant) {
            $stock = ($variant->stock_quantity ?? 0) - ($variant->reserved_stock ?? 0);

            foreach ($variant->attributeValues as $attrVal) {
                if (stripos($attrVal->attribute->name, 'màu') !== false || stripos($attrVal->attribute->name, 'color') !== false) {
                    $colorName = $attrVal->value;
                    
                    // Ưu tiên 1: Mã màu trong DB (meta_value). Ưu tiên 2: Từ điển tự động
                    $colorHex = $attrVal->meta_value ?: $this->getVietnameseColorHex($colorName);

                    // Nếu chưa có màu này trong mảng -> Khởi tạo
                    if (!isset($colors[$colorName])) {
                        $colors[$colorName] = [
                            'name'         => $colorName,
                            'hex'          => $colorHex,
                            'image'        => $this->getImageUrl($variant->image_url ?? $product->thumbnail_image),
                            'out_of_stock' => true // Mặc định hết hàng, sẽ xét kho ở dưới
                        ];
                    } else {
                        // Nếu đã có màu này (nhưng khác size), mà lúc trước chưa có ảnh riêng -> Ghi đè ảnh nếu size này có up ảnh
                        if ($variant->image_url && $colors[$colorName]['image'] === $this->getImageUrl($product->thumbnail_image)) {
                            $colors[$colorName]['image'] = $this->getImageUrl($variant->image_url);
                        }
                    }

                    // Chỉ cần 1 biến thể của màu này còn hàng -> Cả màu đó được tính là còn hàng
                    if ($stock > 0) {
                        $colors[$colorName]['out_of_stock'] = false;
                    }
                }
            }
        }

        return [
            'id'               => $product->id,
            'name'             => $product->name,
            'slug'             => $product->slug,
            'price'            => $currentPrice,
            'old_price'        => $oldPrice,
            'image'            => $this->getImageUrl($product->thumbnail_image),
            'discount_percent' => $discountPercent,
            'is_featured'      => $product->is_featured,
            'colors'           => array_values($colors) // Đưa về mảng chỉ mục 0,1,2... cho Vue dễ lặp
        ];
    }

    public function getNewArrivalsByCategory(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->query('category_id');
            $baseQuery = Product::where('status', 'published')->with(['variants.attributeValues.attribute']);

            if (!$categoryId || $categoryId === 'all') {
                $products = $baseQuery->orderBy('id', 'desc')->limit(8)->get()->map(fn($p) => $this->formatProduct($p));
            } else {
                $catIds = Category::where('parent_id', $categoryId)->pluck('id')->push($categoryId)->toArray();
                
                $products = $baseQuery->whereIn('category_id', $catIds)
                    ->orderBy('id', 'desc')
                    ->limit(8)
                    ->get()
                    ->map(fn($p) => $this->formatProduct($p));
            }

            return response()->json(['success' => true, 'data' => $products]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải tab sản phẩm: ' . $e->getMessage()], 500);
        }
    }
}