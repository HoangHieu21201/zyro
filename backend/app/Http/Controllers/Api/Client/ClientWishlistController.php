<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClientWishlistController extends Controller
{
    public function index(Request $request)
    {
        // Nhận tham số từ Frontend
        $search = $request->query('search', '');
        $sort = $request->query('sort', 'latest');
        $dateFilter = $request->query('date', 'all');
        $page = (int) $request->query('page', 1);
        $perPage = 12; // Tải 12 sản phẩm mỗi lần (Vừa đủ 3 hàng x 4 cột)

        // 1. Query cơ bản
        $query = Wishlist::where('user_id', Auth::id())
            ->with(['product.category.parent', 'product.brand', 'product.variants.attributeValues.attribute']);

        // 2. Bộ lọc theo Ngày/Thời gian thêm vào Wishlist
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateFilter === 'week') {
            $query->where('created_at', '>=', Carbon::now()->startOfWeek());
        } elseif ($dateFilter === 'month') {
            $query->where('created_at', '>=', Carbon::now()->startOfMonth());
        }

        $wishlists = $query->get();

        // 3. Format dữ liệu và chuẩn bị để Sort/Search
        $formattedProducts = collect();
        foreach ($wishlists as $w) {
            if (!$w->product || $w->product->status !== 'published') continue;
            
            $formatted = $this->formatProduct($w->product);
            $formatted['wishlist_added_at'] = $w->created_at; // Lưu lại để sort theo mới/cũ
            $formattedProducts->push($formatted);
        }

        // 4. Tìm kiếm theo tên sản phẩm (Tìm trên mảng đã format)
        if (!empty($search)) {
            $searchStr = mb_strtolower($search, 'UTF-8');
            $formattedProducts = $formattedProducts->filter(function ($item) use ($searchStr) {
                return str_contains(mb_strtolower($item['name'], 'UTF-8'), $searchStr);
            });
        }

        // 5. Sắp xếp (Sort)
        if ($sort === 'price_asc') {
            $formattedProducts = $formattedProducts->sortBy('price')->values();
        } elseif ($sort === 'price_desc') {
            $formattedProducts = $formattedProducts->sortByDesc('price')->values();
        } elseif ($sort === 'oldest') {
            $formattedProducts = $formattedProducts->sortBy('wishlist_added_at')->values();
        } else {
            // Mặc định là mới nhất
            $formattedProducts = $formattedProducts->sortByDesc('wishlist_added_at')->values();
        }

        // 6. Phân trang thủ công (Pagination)
        $totalItems = $formattedProducts->count();
        $paginatedItems = $formattedProducts->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'data' => $paginatedItems,
            'meta' => [
                'current_page' => $page,
                'last_page' => ceil($totalItems / $perPage),
                'total' => $totalItems
            ]
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        $wishlist = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['success' => true, 'is_wishlisted' => false, 'message' => 'Đã bỏ khỏi danh sách yêu thích.']);
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            return response()->json(['success' => true, 'is_wishlisted' => true, 'message' => 'Đã thêm vào danh sách yêu thích.']);
        }
    }

    // ========================================================
    // CÁC HÀM BỔ TRỢ FORMAT DỮ LIỆU
    // ========================================================
    private function getImageUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;
        return env('APP_URL', 'http://127.0.0.1:8000') . '/storage/' . ltrim($path, '/');
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

            foreach ($variant->attributeValues as $attrVal) {
                $attr = $attrVal->attribute;
                $attrName = $attr ? mb_strtolower($attr->name, 'UTF-8') : '';
                
                if (str_contains($attrName, 'màu') || str_contains($attrName, 'color') || str_contains($attrName, 'mau')) {
                    $colorName = $attrVal->value;
                    $colorHex = $attrVal->meta_value ?: $this->getVietnameseColorHex($colorName);
                } else {
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
}