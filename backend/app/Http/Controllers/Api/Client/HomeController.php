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

                // ĐÃ FIX: Eager load thêm category.parent để lấy được danh mục cha
                $flashSale = FlashSale::where('status', 'active')
                    ->where('end_time', '>', now())
                    ->orderBy('start_time', 'asc')
                    ->with(['items.variant.product.category.parent', 'items.variant.product.brand', 'items.variant.product.variants.attributeValues.attribute'])
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

                // ĐÃ FIX: Eager load thêm category.parent cho danh sách chung
                $baseQuery = Product::where('status', 'published')->with(['category.parent', 'brand', 'variants.attributeValues.attribute']);

                $parentCategories = Category::whereNull('parent_id')->where('status', 'active')->orderBy('sort_order')->get(['id', 'name', 'slug']);
                $newArrivalsAll = (clone $baseQuery)->orderBy('id', 'desc')->limit(8)->get()->map(fn($p) => $this->formatProduct($p));

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

                // ĐÃ FIX: Eager load thêm category.parent cho Lookbook
                $lookbooks = Lookbook::where('status', 'published')
                    ->with(['items.product.category.parent', 'items.product.brand', 'items.product.variants.attributeValues.attribute'])
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
                            'description' => $lb->description, // ĐÃ BỔ SUNG: Dùng làm Subtitle trong MegaMenu
                            'main_image' => $this->getImageUrl($lb->main_image),
                            'price_estimate' => $lb->total_price_estimate,
                            'products' => $lbProducts
                        ];
                    });

                // =======================================================
                // ĐÃ THÊM: XÂY DỰNG CÂY DANH MỤC 3 CẤP CHO MEGA MENU
                // =======================================================
                $megaMenuCategories = Category::whereNull('parent_id')
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->with(['children' => function($q) {
                        $q->where('status', 'active')
                          ->orderBy('sort_order')
                          ->with(['products' => function($q2) {
                              // Lấy sẵn 4 sản phẩm mới nhất làm danh sách thả xuống
                              $q2->where('status', 'published')->orderBy('id', 'desc');
                          }]);
                    }])
                    ->get()
                    ->map(function ($parent) {
                        return [
                            'id' => $parent->id,
                            'name' => $parent->name,
                            'slug' => $parent->slug,
                            'children' => $parent->children->map(function ($child) {
                                return [
                                    'id' => $child->id,
                                    'name' => $child->name,
                                    'slug' => $child->slug,
                                    'image' => $this->getImageUrl($child->thumbnail),
                                    // Dùng hàm take(4) của Collection để lấy 4 sản phẩm đầu tiên
                                    'products' => $child->products->take(4)->pluck('name')->toArray()
                                ];
                            })->values()->toArray()
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
                    'lookbooks' => $lookbooks,
                    'mega_menu_categories' => $megaMenuCategories // ĐÃ THÊM: Truyền dữ liệu riêng cho Mega Menu
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
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;
        return 'http://127.0.0.1:8000/storage/' . ltrim($path, '/');
    }

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
        return '#' . substr(md5($colorName), 0, 6);
    }

    // ========================================================
    // ĐÃ FIX: LẤY ĐẦY ĐỦ BRAND, CATEGORY VÀ CHUẨN HÓA LOGIC SIZE
    // ========================================================
    private function formatProduct(Product $product): array
    {
        $defaultVariant = $product->variants->where('is_default', true)->first() ?? $product->variants->first();
        $currentPrice = $defaultVariant ? ($defaultVariant->promotional_price ?? $defaultVariant->price) : $product->base_price;
        $oldPrice     = $defaultVariant && $defaultVariant->promotional_price ? $defaultVariant->price : null;

        $discountPercent = ($oldPrice && $oldPrice > $currentPrice) ? round((($oldPrice - $currentPrice) / $oldPrice) * 100) : null;

        $colors = [];
        $uniqueSizesFallback = []; 

        foreach ($product->variants as $variant) {
            $stock = ($variant->stock_quantity ?? 0) - ($variant->reserved_stock ?? 0);
            $isOutOfStock = $stock <= 0;

            $colorName = null;
            $colorHex = null;
            $sizeName = null;

            // Bóc tách Màu & Size (Kể cả khi người dùng không đặt tên chuẩn)
            foreach ($variant->attributeValues as $attrVal) {
                $attr = $attrVal->attribute;
                $attrName = $attr ? mb_strtolower($attr->name, 'UTF-8') : '';
                
                if (str_contains($attrName, 'màu') || str_contains($attrName, 'color') || str_contains($attrName, 'mau')) {
                    $colorName = $attrVal->value;
                    $colorHex = $attrVal->meta_value ?: $this->getVietnameseColorHex($colorName);
                } else {
                    // Nếu gặp nhiều thuộc tính khác màu, ta ghép nối lại (vd: "S - Cotton")
                    if ($sizeName) {
                        $sizeName .= ' - ' . $attrVal->value;
                    } else {
                        $sizeName = $attrVal->value;
                    }
                }
            }

            if ($sizeName && !isset($uniqueSizesFallback[$sizeName])) {
                $uniqueSizesFallback[$sizeName] = [
                    'name' => $sizeName,
                    'out_of_stock' => $isOutOfStock,
                    'variant_id' => $variant->id
                ];
            }

            if ($colorName) {
                if (!isset($colors[$colorName])) {
                    $colors[$colorName] = [
                        'name'         => $colorName,
                        'hex'          => $colorHex,
                        'image'        => $this->getImageUrl($variant->image_url ?? $product->thumbnail_image),
                        'out_of_stock' => true,
                        'sizes'        => [] 
                    ];
                }

                if ($variant->image_url && $colors[$colorName]['image'] === $this->getImageUrl($product->thumbnail_image)) {
                    $colors[$colorName]['image'] = $this->getImageUrl($variant->image_url);
                }

                if (!$isOutOfStock) {
                    $colors[$colorName]['out_of_stock'] = false;
                }

                // Gắn size vào mảng sizes của màu tương ứng (Chống lặp)
                if ($sizeName) {
                    $exists = false;
                    foreach ($colors[$colorName]['sizes'] as $s) {
                        if ($s['name'] === $sizeName) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $colors[$colorName]['sizes'][] = [
                            'name'         => $sizeName,
                            'out_of_stock' => $isOutOfStock,
                            'variant_id'   => $variant->id
                        ];
                    }
                }
            }
        }

        if (empty($colors) && !empty($uniqueSizesFallback)) {
            $colors['Mặc định'] = [
                'name' => 'Mặc định',
                'hex' => '#f8f9fa',
                'image' => $this->getImageUrl($product->thumbnail_image),
                'out_of_stock' => collect($uniqueSizesFallback)->every('out_of_stock', true),
                'sizes' => array_values($uniqueSizesFallback)
            ];
        }

        // ĐÃ BỔ SUNG: Ghép chuỗi Danh mục Cha | Danh mục Con
        $categoryData = null;
        if ($product->category) {
            $catName = $product->category->parent ? $product->category->parent->name . ' | ' . $product->category->name : $product->category->name;
            $categoryData = [
                'id' => $product->category->id,
                'name' => $catName
            ];
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
            'category'         => $categoryData,
            'brand'            => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name] : null,
            'colors'           => array_values($colors) 
        ];
    }

    public function getNewArrivalsByCategory(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->query('category_id');
            // ĐÃ FIX: Eager load chuẩn chỉnh
            $baseQuery = Product::where('status', 'published')->with(['category.parent', 'brand', 'variants.attributeValues.attribute']);

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
            return response()->json(['success' => false, 'message' => 'Lỗi tìm kiếm: ' . $e->getMessage()], 500);
        }
    }

    // ========================================================
    // ĐÃ THÊM: HÀM LẤY CHI TIẾT SẢN PHẨM (PRODUCT DETAIL)
    // ========================================================
    public function getProductDetail($id): JsonResponse
    {
        try {
            // Lấy SP kèm theo Danh mục, Thương hiệu, Thư viện ảnh, Biến thể
            $product = Product::where('status', 'published')
                ->with(['category.parent', 'brand', 'images', 'variants.attributeValues.attribute'])
                ->findOrFail($id);

            // Tái sử dụng hàm format chuẩn của hệ thống để lấy màu sắc, size
            $formatted = $this->formatProduct($product);
            
            // Bổ sung các trường chi tiết cần thiết cho riêng trang Detail
            $formatted['description'] = $product->description;
            $formatted['care_instructions'] = $product->care_instructions;
            $formatted['specifications'] = $product->specifications;
            $formatted['size_guide_url'] = $product->size_guide_url;
            $formatted['sku'] = $product->variants->first()?->sku ?? 'N/A';
            
            // Bổ sung mảng Thư viện ảnh (Gallery)
            $gallery = [];
            if ($product->thumbnail_image) {
                $gallery[] = $this->getImageUrl($product->thumbnail_image);
            }
            foreach ($product->images->sortBy('sort_order') as $img) {
                $gallery[] = $this->getImageUrl($img->image_url);
            }
            $formatted['gallery'] = array_values(array_unique($gallery));

            return response()->json(['success' => true, 'data' => $formatted]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm'], 404);
        }
    }

    // ========================================================
    // ĐÃ THÊM: HÀM LẤY DỮ LIỆU TRANG DANH MỤC SẢN PHẨM
    // ========================================================
    public function getCategoryPageData(Request $request): JsonResponse
    {
        try {
            $slug = $request->query('slug');
            $search = $request->query('search');
            $sort = $request->query('sort', 'newest');

            $category = null;
            $subCategories = collect([]);

            $baseQuery = Product::where('status', 'published')->with(['category.parent', 'brand', 'variants.attributeValues.attribute']);

            // 1. Nếu có Slug danh mục -> Lọc theo Cây danh mục
            if ($slug) {
                $category = Category::where('slug', $slug)->where('status', 'active')->first();
                if ($category) {
                    $catIds = Category::where('parent_id', $category->id)->pluck('id')->push($category->id)->toArray();
                    $baseQuery->whereIn('category_id', $catIds);
                    
                    if ($category->parent_id) {
                        // Nếu là danh mục con -> Hiện các anh em cùng cấp
                        $subCategories = Category::where('parent_id', $category->parent_id)->where('status', 'active')->get();
                    } else {
                        // Nếu là danh mục cha -> Hiện các con của nó
                        $subCategories = Category::where('parent_id', $category->id)->where('status', 'active')->get();
                    }
                }
            } else {
                // Nếu không có slug, lấy các danh mục gốc
                $subCategories = Category::whereNull('parent_id')->where('status', 'active')->get();
            }

            // 2. Nếu có từ khóa tìm kiếm (Từ Modal truyền qua)
            if ($search) {
                $baseQuery->where(function($q) use ($search) {
                    $q->where('name', 'ilike', '%' . $search . '%')
                      ->orWhere('slug', 'ilike', '%' . $search . '%');
                });
            }

            // 3. Sắp xếp
            switch ($sort) {
                case 'best_sales': $baseQuery->orderBy('sales_count', 'desc'); break;
                case 'price_asc': $baseQuery->orderBy('base_price', 'asc'); break;
                case 'price_desc': $baseQuery->orderBy('base_price', 'desc'); break;
                case 'newest': default: $baseQuery->orderBy('id', 'desc'); break;
            }

            // 4. Lấy 4 SP nổi bật (Top View) cho khung đỏ
            $highlightProducts = (clone $baseQuery)->orderBy('view_count', 'desc')->limit(4)->get()->map(fn($p) => $this->formatProduct($p));
            
            // 5. Phân trang SP chính (Mỗi lần tải thêm 12 SP)
            $productsPaginator = $baseQuery->paginate(12);
            $mainProducts = collect($productsPaginator->items())->map(fn($p) => $this->formatProduct($p));

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $category ? ['name' => $category->name, 'slug' => $category->slug, 'thumbnail' => $this->getImageUrl($category->thumbnail)] : null,
                    'sub_categories' => $subCategories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'image' => $this->getImageUrl($c->thumbnail)]),
                    'highlight_products' => $highlightProducts,
                    'products' => $mainProducts,
                    'current_page' => $productsPaginator->currentPage(),
                    'last_page' => $productsPaginator->lastPage(),
                    'total' => $productsPaginator->total()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ========================================================
    // ĐÃ FIX: TRÍCH XUẤT FULL DANH MỤC & NHẬN FILTER TỪ FRONTEND
    // ========================================================
    public function getFlashSalePageData(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $categoryFilter = $request->query('category', 'all'); // Lọc danh mục
            $sortFilter = $request->query('sort', 'default');     // Sắp xếp
            $perPage = 12;

            $flashSale = FlashSale::where('status', 'active')
                ->where('end_time', '>', now())
                ->orderBy('start_time', 'asc')
                ->with(['items.variant.product.category.parent', 'items.variant.product.brand', 'items.variant.product.variants.attributeValues.attribute'])
                ->first();

            if (!$flashSale) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'flash_sale' => null,
                        'available_categories' => [],
                        'products' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0]
                    ]
                ]);
            }

            $fsProducts = [];
            
            // 1. Trích xuất sản phẩm và tính giá
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

            $fsProductsList = array_values($fsProducts);

            // 2. LẤY ĐẦY ĐỦ CÁC DANH MỤC TRƯỚC KHI LỌC/PHÂN TRANG
            $availableCategories = [];
            foreach ($fsProductsList as $p) {
                $catName = isset($p['category']['name']) ? explode(' | ', $p['category']['name'])[0] : 'Sản phẩm khác';
                if (!in_array($catName, $availableCategories)) {
                    $availableCategories[] = $catName;
                }
            }
            sort($availableCategories);

            // 3. LỌC THEO DANH MỤC TỪ FRONTEND
            if ($categoryFilter !== 'all') {
                $fsProductsList = array_filter($fsProductsList, function($p) use ($categoryFilter) {
                    $catName = isset($p['category']['name']) ? explode(' | ', $p['category']['name'])[0] : 'Sản phẩm khác';
                    return $catName === $categoryFilter;
                });
            }

            // 4. SẮP XẾP THEO FRONTEND
            if ($sortFilter === 'price_asc') {
                usort($fsProductsList, fn($a, $b) => $a['price'] <=> $b['price']);
            } elseif ($sortFilter === 'price_desc') {
                usort($fsProductsList, fn($a, $b) => $b['price'] <=> $a['price']);
            } elseif ($sortFilter === 'discount_desc') {
                usort($fsProductsList, fn($a, $b) => ($b['discount_percent'] ?? 0) <=> ($a['discount_percent'] ?? 0));
            }

            // 5. PHÂN TRANG
            $total = count($fsProductsList);
            $lastPage = ceil($total / $perPage) ?: 1;
            $offset = ($page - 1) * $perPage;
            $paginatedProducts = array_slice($fsProductsList, $offset, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'flash_sale' => [
                        'id'           => $flashSale->id,
                        'name'         => $flashSale->name,
                        'banner'       => $this->getImageUrl($flashSale->banner_image),
                        'start_time'   => $flashSale->start_time,
                        'end_time'     => $flashSale->end_time,
                    ],
                    'available_categories' => $availableCategories,
                    'products' => [
                        'data'         => array_values($paginatedProducts),
                        'current_page' => $page,
                        'last_page'    => $lastPage,
                        'total'        => $total
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải trang Flash Sale: ' . $e->getMessage()], 500);
        }
    }

    // ========================================================
    // ĐÃ THÊM MỚI: HÀM LẤY DANH SÁCH LOOKBOOK (INDEX PAGE)
    // ========================================================
    public function getLookbookPageData(Request $request): JsonResponse
    {
        try {
            // Phân trang mỗi lần tải 8 lookbook
            $paginator = Lookbook::where('status', 'published')
                ->with(['items.product.category.parent', 'items.product.brand', 'items.product.variants.attributeValues.attribute'])
                ->orderBy('id', 'desc')
                ->paginate(8);

            $lookbooks = collect($paginator->items())->map(function ($lb) {
                // Tái sử dụng logic formatProduct cho các SP bên trong
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
                    'description' => $lb->description,
                    'main_image' => $this->getImageUrl($lb->main_image),
                    'price_estimate' => $lb->total_price_estimate,
                    'products' => $lbProducts // Trả về SP để frontend có thể preview nhanh nếu muốn
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'lookbooks' => $lookbooks,
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'total' => $paginator->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải trang Lookbook: ' . $e->getMessage()], 500);
        }
    }

    // ========================================================
    // ĐÃ THÊM MỚI: HÀM LẤY CHI TIẾT 1 LOOKBOOK (DETAIL PAGE)
    // ========================================================
    public function getLookbookDetail($slug): JsonResponse
    {
        try {
            $lb = Lookbook::where('slug', $slug)
                ->where('status', 'published')
                ->with(['items.product.category.parent', 'items.product.brand', 'items.product.variants.attributeValues.attribute'])
                ->firstOrFail();

            $lbProducts = $lb->items->map(function ($item) {
                if ($item->product && $item->product->status === 'published') {
                    return $this->formatProduct($item->product);
                }
                return null;
            })->filter()->values();

            $data = [
                'id' => $lb->id,
                'name' => $lb->name,
                'slug' => $lb->slug,
                'description' => $lb->description,
                'main_image' => $this->getImageUrl($lb->main_image),
                'price_estimate' => $lb->total_price_estimate,
                'products' => $lbProducts
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy Bộ sưu tập này'], 404);
        }
    }
}