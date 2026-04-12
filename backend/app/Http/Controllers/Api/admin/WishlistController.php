<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    // Thống kê Sản phẩm được yêu thích
    public function index(Request $request): JsonResponse
    {
        try {
            $cacheKey = sprintf('wishlists_admin_stats_p%s_c%s_b%s_s%s_sort%s',
                $request->get('page', 1),
                $request->get('category_id', 'all'),
                $request->get('brand_id', 'all'),
                Str::slug($request->get('search', 'all')),
                $request->get('sort', 'desc')
            );

            $result = Cache::remember($cacheKey, 300, function () use ($request) {
                $totalLikes = Wishlist::count();

                $topCategory = DB::table('wishlists')
                    ->join('products', 'wishlists.product_id', '=', 'products.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->select('categories.name', DB::raw('COUNT(wishlists.id) as likes_count'))
                    ->whereNull('products.deleted_at')
                    ->groupBy('categories.id', 'categories.name')
                    ->orderByDesc('likes_count')
                    ->first();

                $topBrand = DB::table('wishlists')
                    ->join('products', 'wishlists.product_id', '=', 'products.id')
                    ->join('brands', 'products.brand_id', '=', 'brands.id')
                    ->select('brands.name', DB::raw('COUNT(wishlists.id) as likes_count'))
                    ->whereNull('products.deleted_at')
                    ->groupBy('brands.id', 'brands.name')
                    ->orderByDesc('likes_count')
                    ->first();

                $query = Wishlist::select('wishlists.product_id', DB::raw('COUNT(wishlists.id) as likes_count'))
                    ->groupBy('wishlists.product_id');

                if ($request->filled('category_id') || $request->filled('brand_id') || $request->filled('search')) {
                    $query->join('products', 'wishlists.product_id', '=', 'products.id');

                    if ($request->filled('category_id')) $query->where('products.category_id', $request->category_id);
                    if ($request->filled('brand_id')) $query->where('products.brand_id', $request->brand_id);
                    if ($request->filled('search')) $query->where('products.name', 'ILIKE', '%' . $request->search . '%');
                }

                $sort = $request->sort === 'asc' ? 'asc' : 'desc';
                $query->orderBy('likes_count', $sort);

                $wishlists = $query->with(['product' => function ($q) {
                    $q->withTrashed()
                        ->select('id', 'name', 'slug', 'thumbnail_image', 'base_price', 'status', 'deleted_at', 'category_id', 'brand_id')
                        ->with(['category:id,name', 'brand:id,name']);
                }])->paginate(12);

                return [
                    'stats' => [
                        'total_likes'  => $totalLikes,
                        'top_category' => $topCategory ? ['name' => $topCategory->name, 'count' => $topCategory->likes_count] : null,
                        'top_brand'    => $topBrand ? ['name' => $topBrand->name, 'count' => $topBrand->likes_count] : null,
                    ],
                    'data' => $wishlists
                ];
            });

            return response()->json([
                'success' => true,
                'stats'   => $result['stats'],
                'data'    => $result['data']
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi thống kê: ' . $e->getMessage()], 500);
        }
    }
}