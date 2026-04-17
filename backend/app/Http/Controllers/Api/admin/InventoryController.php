<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Lookbook; 
use Illuminate\Support\Facades\DB;

use App\Http\Requests\Admin\UpdateVariantStockRequest;
use App\Http\Requests\Admin\BulkUpdateVariantStockRequest;
use App\Http\Requests\Admin\UpdateLookbookLimitRequest;

class InventoryController extends Controller
{
    /**
     * 1. LẤY DANH SÁCH BIẾN THỂ (Chỉ lấy SP Đang bán, bỏ qua SP ẩn/nháp/xóa)
     */
    public function getVariants()
    {
        $variants = ProductVariant::whereHas('product', function ($query) {
            $query->where('status', 'published'); // Chỉ lấy SP public
        })->with([
            'product:id,name,slug,category_id,base_price,status,thumbnail_image',
            'product.category:id,name'
        ])->get();


        return response()->json([
            'success' => true,
            'data'    => $variants
        ]);
    }

    /**
     * 2. CẬP NHẬT KHO BIẾN THỂ (Đơn lẻ)
     */
    public function updateVariantStock(UpdateVariantStockRequest $request, $id)
    {
        $type = $request->validated('type');
        $quantity = $request->validated('quantity');

        DB::beginTransaction();
        try {
            $variant = ProductVariant::where('id', $id)->lockForUpdate()->firstOrFail();
            
            if ($type === 'add') {
                $variant->stock_quantity += $quantity;
            } else {
                if ($variant->stock_quantity < $quantity) {
                    DB::rollBack();
                    return response()->json(['message' => "Lỗi: {$variant->sku} chỉ còn {$variant->stock_quantity} SP, không đủ để xuất $quantity!"], 422);
                }
                $variant->stock_quantity -= $quantity;
            }

            $variant->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $type === 'add' ? "Đã nhập kho +$quantity." : "Đã xuất kho -$quantity.",
                'data'    => $variant
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi cập nhật. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * 3. CẬP NHẬT HÀNG LOẠT (BULK UPDATE)
     */
    public function bulkUpdateVariantStock(BulkUpdateVariantStockRequest $request)
    {
        $type = $request->validated('type');
        $quantity = $request->validated('quantity');
        $ids = $request->validated('variant_ids');

        DB::beginTransaction();
        try {
            // Sắp xếp ID để tránh Deadlock khi nhiều Admin thao tác cùng lúc
            sort($ids);
            
            $variants = ProductVariant::whereIn('id', $ids)->lockForUpdate()->get();

            foreach ($variants as $variant) {
                if ($type === 'add') {
                    $variant->stock_quantity += $quantity;
                } else {
                    if ($variant->stock_quantity < $quantity) {
                        DB::rollBack();
                        // Báo lỗi cụ thể mã SKU nào thiếu hàng
                        return response()->json(['message' => "Hủy toàn bộ thao tác. Lỗi ở SKU [{$variant->sku}]: Chỉ còn {$variant->stock_quantity} SP, không đủ để xuất {$quantity}!"], 422);
                    }
                    $variant->stock_quantity -= $quantity;
                }
                $variant->save();
            }

            DB::commit();

            // Gắn ảnh fallback trước khi trả về data cho frontend
            $variants->transform(function ($variant) {
                if (empty($variant->image_url)) {
                    $variant->image_url = '/client_placeholder.png';
                }
                return $variant;
            });

            return response()->json([
                'success' => true,
                'message' => $type === 'add' ? "Đã nhập đồng loạt +$quantity SP cho " . count($ids) . " mặt hàng." : "Đã trừ đồng loạt -$quantity SP cho " . count($ids) . " mặt hàng.",
                // Trả về danh sách để Frontend tự cập nhật
                'data'    => $variants 
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Hệ thống đang bận, vui lòng thử lại sau.'], 500);
        }
    }

    /**
     * 4. LẤY DANH SÁCH LOOKBOOK (Chỉ lấy Lookbook đang hiển thị)
     */
    public function getLookbooks()
    {
        $lookbooks = Lookbook::where('status', 'published')->orderBy('id', 'desc')->get();

        // Bổ sung logic ảnh thay thế cho frontend
        $lookbooks->transform(function ($lookbook) {
            if (empty($lookbook->main_image)) {
                $lookbook->main_image = '/client_placeholder.png';
            }
            return $lookbook;
        });

        return response()->json([
            'success' => true,
            'data'    => $lookbooks
        ]);
    }

    /**
     * 5. CẬP NHẬT GIỚI HẠN LOOKBOOK
     */
    public function updateLookbookLimit(UpdateLookbookLimitRequest $request, $id)
    {
        $lookbook = Lookbook::findOrFail($id);
        $lookbook->usage_limit = $request->validated('usage_limit');
        $lookbook->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật giới hạn Lookbook thành công',
            'data'    => $lookbook
        ]);
    }
}