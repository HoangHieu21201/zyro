<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Events\ProductEvent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $products = Product::with(['category', 'brand'])
                ->withCount('variants') 
                ->orderBy('id', 'desc')
                ->withTrashed()
                ->get();
                
            return response()->json(['success' => true, 'data' => $products]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = DB::transaction(function () use ($request) {
                $data = $request->validated();
                $data['is_featured'] = filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($request->hasFile('thumbnail_image')) {
                    $data['thumbnail_image'] = $request->file('thumbnail_image')->store('products/thumbnails', 'public');
                }

                $product = Product::create($data);

                // Xử lý Variants (Bao gồm Cost Price)
                $variantsData = json_decode($request->input('variants_data'), true);
                if (is_array($variantsData)) {
                    foreach ($variantsData as $index => $vData) {
                        $variantImageUrl = null;
                        $fileKey = "variant_image_{$index}";
                        if ($request->hasFile($fileKey)) {
                            $variantImageUrl = $request->file($fileKey)->store('products/variants', 'public');
                        }

                        $variant = $product->variants()->create([
                            'sku'               => $vData['sku'],
                            'cost_price'        => $vData['cost_price'] ?? 0,
                            'price'             => $vData['price'],
                            'promotional_price' => $vData['promotional_price'] ?? null,
                            'stock_quantity'    => $vData['stock_quantity'],
                            'reserved_stock'    => 0,
                            'image_url'         => $variantImageUrl,
                            'is_default'        => $index === 0, 
                        ]);

                        if (!empty($vData['attributes'])) {
                            $attributeValueIds = array_filter(array_values($vData['attributes']));
                            if (count($attributeValueIds) > 0) {
                                $variant->attributeValues()->attach($attributeValueIds);
                            }
                        }
                    }
                }

                // Xử lý Thư viện ảnh (Gallery)
                if ($request->hasFile('gallery_images')) {
                    foreach ($request->file('gallery_images') as $idx => $file) {
                        $product->images()->create([
                            'image_url'  => $file->store('products/gallery', 'public'),
                            'sort_order' => $idx
                        ]);
                    }
                }

                return $product;
            });

            $product->load(['category', 'brand']);
            broadcast(new ProductEvent('created', $product))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã tạo sản phẩm thành công!', 'data' => $product], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $product = Product::withTrashed()
                ->with(['category', 'brand', 'images'])
                ->with(['variants.attributeValues.attribute'])
                ->findOrFail($id);
                
            return response()->json(['success' => true, 'data' => $product]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm.'], 404);
        }
    }

    // ĐÃ THÊM: Cập nhật siêu tốc Trạng thái từ bảng Index
    public function updateStatus(\Illuminate\Http\Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:published,draft,hidden']);
        try {
            $product = Product::findOrFail($id);
            $product->update(['status' => $request->status]);
            
            $product->load(['category', 'brand']);
            broadcast(new ProductEvent('updated', $product))->toOthers();
            
            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ĐÃ THÊM: Logic Update toàn bộ Sản phẩm từ màn Edit.vue
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        try {
            $product = DB::transaction(function () use ($request, $id) {
                $product = Product::findOrFail($id);
                $data = $request->validated();
                $data['is_featured'] = filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN);

                // Nếu có ảnh đại diện mới thì xóa ảnh cũ đi
                if ($request->hasFile('thumbnail_image')) {
                    if ($product->thumbnail_image) Storage::disk('public')->delete($product->thumbnail_image);
                    $data['thumbnail_image'] = $request->file('thumbnail_image')->store('products/thumbnails', 'public');
                }

                $product->update($data);

                // 1. Xử lý Cập nhật / Thêm mới / Xóa Variants
                $variantsData = json_decode($request->input('variants_data'), true);
                if (is_array($variantsData)) {
                    // Lấy danh sách SKU gửi lên để giữ lại, cái nào ko có trong danh sách thì XÓA (Soft delete)
                    $incomingSkus = array_column($variantsData, 'sku');
                    $product->variants()->whereNotIn('sku', $incomingSkus)->delete();

                    foreach ($variantsData as $index => $vData) {
                        $variant = $product->variants()->withTrashed()->where('sku', $vData['sku'])->first();
                        
                        $variantDataToSave = [
                            'sku'               => $vData['sku'],
                            'cost_price'        => $vData['cost_price'] ?? 0,
                            'price'             => $vData['price'],
                            'promotional_price' => $vData['promotional_price'] ?? null,
                            'stock_quantity'    => $vData['stock_quantity'],
                            'is_default'        => $index === 0, 
                        ];

                        $fileKey = "variant_image_{$index}";
                        if ($request->hasFile($fileKey)) {
                            if ($variant && $variant->image_url) Storage::disk('public')->delete($variant->image_url);
                            $variantDataToSave['image_url'] = $request->file($fileKey)->store('products/variants', 'public');
                        }

                        if ($variant) {
                            if ($variant->trashed()) $variant->restore(); // Khôi phục nếu bị soft delete nhầm
                            $variant->update($variantDataToSave);
                        } else {
                            $variantDataToSave['reserved_stock'] = 0;
                            $variant = $product->variants()->create($variantDataToSave);
                        }

                        // Cập nhật lại Mảng Thuộc tính Trung gian (Pivot)
                        if (!empty($vData['attributes'])) {
                            $attributeValueIds = array_filter(array_values($vData['attributes']));
                            $variant->attributeValues()->sync($attributeValueIds);
                        } else {
                            $variant->attributeValues()->detach();
                        }
                    }
                }

                // 2. Xử lý Xóa ảnh Gallery cũ
                if ($request->filled('deleted_gallery_ids')) {
                    $deletedIds = json_decode($request->input('deleted_gallery_ids'), true);
                    if (is_array($deletedIds) && count($deletedIds) > 0) {
                        $imagesToDelete = $product->images()->whereIn('id', $deletedIds)->get();
                        foreach ($imagesToDelete as $img) {
                            Storage::disk('public')->delete($img->image_url);
                            $img->delete();
                        }
                    }
                }

                // 3. Thêm ảnh Gallery mới
                if ($request->hasFile('gallery_images')) {
                    $maxSort = $product->images()->max('sort_order') ?? -1;
                    foreach ($request->file('gallery_images') as $idx => $file) {
                        $product->images()->create([
                            'image_url'  => $file->store('products/gallery', 'public'),
                            'sort_order' => $maxSort + $idx + 1
                        ]);
                    }
                }

                return $product;
            });

            $product->load(['category', 'brand', 'images']);
            $product->load(['variants.attributeValues.attribute']);
            broadcast(new ProductEvent('updated', $product))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã cập nhật sản phẩm thành công!', 'data' => $product]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $product->slug = $product->slug . '-deleted-' . time();
            $product->save();
            $product->delete();

            broadcast(new ProductEvent('deleted', $product))->toOthers();
            return response()->json(['success' => true, 'message' => 'Đã đưa vào thùng rác!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $product = Product::withTrashed()->findOrFail($id);
            $originalSlug = preg_replace('/-deleted-\d+$/', '', $product->slug);
            
            if (Product::where('slug', $originalSlug)->whereNull('deleted_at')->exists()) {
                return response()->json(['success' => false, 'message' => 'Slug đã bị trùng.'], 400);
            }

            $product->slug = $originalSlug;
            $product->save();
            $product->restore();
            
            $product->load(['category', 'brand']);
            broadcast(new ProductEvent('restored', $product))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã khôi phục thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}