<?php

// File: backend/app/Http/Controllers/Api/Admin/BrandController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Events\BrandEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; // BẮT BUỘC IMPORT CACHE CỦA REDIS
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    // Đã khai báo Tên Chìa Khóa (Key) cho Redis để dễ quản lý
    private string $cacheKey = 'brands_list_all';

    private function getNextSortOrder(): int
    {
        $max = Brand::max('sort_order');
        return is_numeric($max) ? $max + 1 : 1;
    }

    public function index(): JsonResponse
    {
        try {
            $brands = Cache::remember($this->cacheKey, 86400, function () {
                return Brand::orderByRaw('sort_order IS NULL, sort_order ASC')
                    ->orderBy('id', 'desc')
                    ->withTrashed()
                    ->get();
            });
                
            return response()->json(['success' => true, 'data' => $brands]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['slug'] = $this->generateUniqueSlug($data['name']);
            $data['status'] = $data['status'] ?? 'active';
            
            // Tự động cấp phát số thứ tự nếu Active
            $data['sort_order'] = ($data['status'] === 'active') ? $this->getNextSortOrder() : null;

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('brands/logos', 'public');
            }

            $brand = Brand::create($data);

            // REDIS: Xóa Cache cũ
            Cache::forget($this->cacheKey);

            broadcast(new BrandEvent('created', $brand))->toOthers();

            return response()->json(['success' => true, 'message' => 'Tạo thương hiệu thành công!', 'data' => $brand], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            // Chi tiết 1 Thương hiệu không cần Cache vì nó query theo ID (PK) rất nhanh
            $brand = Brand::withTrashed()->findOrFail($id);
            return response()->json(['success' => true, 'data' => $brand]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thương hiệu này.'], 404);
        }
    }

    public function update(UpdateBrandRequest $request, $id): JsonResponse
    {
        try {
            $brand = Brand::findOrFail($id);
            $data = $request->validated();

            if ($data['name'] !== $brand->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $brand->id);
            }

            // Tự động xử lý vị trí khi đổi trạng thái hiển thị
            if (isset($data['status']) && $brand->status !== $data['status']) {
                $data['sort_order'] = ($data['status'] === 'active') ? $this->getNextSortOrder() : null;
            }

            // Quản lý Logo
            if ($request->hasFile('logo')) {
                if ($brand->logo) Storage::disk('public')->delete($brand->logo);
                $data['logo'] = $request->file('logo')->store('brands/logos', 'public');
            } elseif (isset($data['remove_logo']) && filter_var($data['remove_logo'], FILTER_VALIDATE_BOOLEAN)) {
                if ($brand->logo) Storage::disk('public')->delete($brand->logo);
                $data['logo'] = null;
            }

            $brand->update($data);

            // REDIS: Xóa Cache cũ
            Cache::forget($this->cacheKey);

            broadcast(new BrandEvent('updated', $brand))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật thương hiệu thành công!', 'data' => $brand]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $brand = Brand::findOrFail($id);

            if ($brand->products()->exists()) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa do thương hiệu này đang liên kết với sản phẩm!'], 422);
            }

            // Xóa mềm: Tước vị trí ưu tiên và gắn cờ Xóa vào Slug
            $brand->sort_order = null;
            $brand->slug = $brand->slug . '-deleted-' . time();
            $brand->save();

            $brand->delete();

            // REDIS: Xóa Cache cũ
            Cache::forget($this->cacheKey);

            broadcast(new BrandEvent('deleted', $brand))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã đưa thương hiệu vào thùng rác!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $brand = Brand::withTrashed()->findOrFail($id);

            $originalSlug = preg_replace('/-deleted-\d+$/', '', $brand->slug);
            if (Brand::where('slug', $originalSlug)->whereNull('deleted_at')->exists()) {
                return response()->json(['success' => false, 'message' => 'Tên thương hiệu này đã bị tài khoản khác lấy mất trong thời gian bị xóa.'], 400);
            }

            $brand->slug = $originalSlug;

            if ($brand->status === 'active') {
                $brand->sort_order = $this->getNextSortOrder();
            }

            $brand->save();
            $brand->restore();

            // REDIS: Xóa Cache cũ
            Cache::forget($this->cacheKey);

            broadcast(new BrandEvent('restored', $brand))->toOthers();

            return response()->json(['success' => true, 'message' => 'Khôi phục thương hiệu thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'brands' => 'required|array',
            'brands.*.id' => 'required|exists:brands,id',
            'brands.*.sort_order' => 'required|integer|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->brands as $item) {
                    Brand::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
                }
            });

            // REDIS: Xóa Cache cũ
            Cache::forget($this->cacheKey);

            return response()->json(['success' => true, 'message' => 'Cập nhật thứ tự hiển thị thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    private function generateUniqueSlug($name, $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Brand::where('slug', $slug)->where('id', '!=', $ignoreId)->withTrashed()->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}