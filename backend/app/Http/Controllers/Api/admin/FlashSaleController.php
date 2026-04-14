<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Http\Requests\FlashSale\StoreFlashSaleRequest;
use App\Http\Requests\FlashSale\UpdateFlashSaleRequest;
use App\Events\FlashSaleEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class FlashSaleController extends Controller
{
    private string $cacheVersionKey = 'flash_sales_admin_cache_version';

    private function clearCache(): void
    {
        Cache::increment($this->cacheVersionKey);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $version = Cache::rememberForever($this->cacheVersionKey, fn() => 1);
            $cacheKey = sprintf('flash_sales_v%s_p%s_s%s_q%s', $version, $request->get('page', 1), $request->get('status', 'all'), $request->get('search', ''));

            $flashSales = Cache::remember($cacheKey, 86400, function() use ($request) {
                $query = FlashSale::withCount('items')->orderBy('id', 'desc');

                if ($request->has('status') && $request->status !== 'all') {
                    $query->where('status', $request->status);
                }

                if ($request->has('search') && $request->search !== '') {
                    $query->where('name', 'ILIKE', '%' . $request->search . '%')
                          ->orWhere('slug', 'ILIKE', '%' . $request->search . '%');
                }

                return $query->paginate(12);
            });

            // Lấy thêm đếm số lượng cho Tabs
            $counts = Cache::remember("flash_sales_counts_v{$version}", 86400, function() {
                return [
                    'all' => FlashSale::count(),
                    'active' => FlashSale::where('status', 'active')->count(),
                    'hidden' => FlashSale::where('status', 'hidden')->count(),
                    'ended' => FlashSale::where('status', 'ended')->count(),
                ];
            });

            return response()->json(['success' => true, 'data' => $flashSales, 'counts' => $counts]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreFlashSaleRequest $request): JsonResponse
    {
        try {
            $flashSale = DB::transaction(function () use ($request) {
                $data = $request->validated();

                if ($request->hasFile('banner_image')) {
                    $data['banner_image'] = $request->file('banner_image')->store('flash_sales/banners', 'public');
                }

                $flashSale = FlashSale::create($data);

                $itemsData = json_decode($request->input('items_data'), true);
                if (is_array($itemsData)) {
                    foreach ($itemsData as $item) {
                        FlashSaleItem::create([
                            'flash_sale_id'    => $flashSale->id,
                            'variant_id'       => $item['variant_id'],
                            'flash_sale_price' => $item['flash_sale_price'],
                            'quantity_limit'   => $item['quantity_limit'] ?? 0,
                            'sold_quantity'    => 0,
                        ]);
                    }
                }

                return $flashSale;
            });

            $this->clearCache();
            
            $flashSale->load('items.variant.product');
            broadcast(new FlashSaleEvent('created', $flashSale))->toOthers();

            return response()->json(['success' => true, 'message' => 'Tạo Flash Sale thành công!', 'data' => $flashSale], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $flashSale = FlashSale::with(['items.variant.product', 'items.variant.attributeValues.attribute'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $flashSale]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy Flash Sale.'], 404);
        }
    }

    public function update(UpdateFlashSaleRequest $request, $id): JsonResponse
    {
        try {
            $flashSale = DB::transaction(function () use ($request, $id) {
                $flashSale = FlashSale::findOrFail($id);
                $data = $request->validated();

                if ($request->hasFile('banner_image')) {
                    if ($flashSale->banner_image) Storage::disk('public')->delete($flashSale->banner_image);
                    $data['banner_image'] = $request->file('banner_image')->store('flash_sales/banners', 'public');
                } elseif (isset($data['remove_banner']) && filter_var($data['remove_banner'], FILTER_VALIDATE_BOOLEAN)) {
                    if ($flashSale->banner_image) Storage::disk('public')->delete($flashSale->banner_image);
                    $data['banner_image'] = null;
                }

                $flashSale->update($data);

                FlashSaleItem::where('flash_sale_id', $flashSale->id)->delete();
                
                $itemsData = json_decode($request->input('items_data'), true);
                if (is_array($itemsData)) {
                    foreach ($itemsData as $item) {
                        FlashSaleItem::create([
                            'flash_sale_id'    => $flashSale->id,
                            'variant_id'       => $item['variant_id'],
                            'flash_sale_price' => $item['flash_sale_price'],
                            'quantity_limit'   => $item['quantity_limit'] ?? 0,
                            'sold_quantity'    => $item['sold_quantity'] ?? 0,
                        ]);
                    }
                }

                return $flashSale;
            });

            $this->clearCache();

            $flashSale->load('items.variant.product');
            broadcast(new FlashSaleEvent('updated', $flashSale))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật Flash Sale thành công!', 'data' => $flashSale]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,hidden,ended']);
        try {
            $flashSale = FlashSale::findOrFail($id);
            $flashSale->update(['status' => $request->status]);

            $this->clearCache();
            broadcast(new FlashSaleEvent('updated', $flashSale))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $flashSale = FlashSale::findOrFail($id);
            if ($flashSale->banner_image) Storage::disk('public')->delete($flashSale->banner_image);
            
            FlashSaleItem::where('flash_sale_id', $id)->delete();
            $flashSale->delete();

            $this->clearCache();
            broadcast(new FlashSaleEvent('deleted', $flashSale))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã xóa chiến dịch Flash Sale.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()], 500);
        }
    }
}