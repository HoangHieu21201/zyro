<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Events\CategoryEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private function getNextSortOrder(): int
    {
        $max = Category::max('sort_order');
        return is_numeric($max) ? $max + 1 : 1;
    }

    private function clearCache(Category $category = null): void
    {
        Cache::forget('categories_list_all');
        Cache::forget('categories_list_root');
        Cache::forget('categories_tree_all');
        
        if ($category && $category->parent_id) {
            Cache::forget('categories_list_' . $category->parent_id);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $parentId = $request->has('parent_id') ? $request->parent_id : 'all';
            $cacheKey = 'categories_list_' . ($parentId === 'null' || $parentId === '' ? 'root' : $parentId);

            $categories = Cache::remember($cacheKey, 86400, function () use ($request) {
                $query = Category::with('parent')->withCount('children');

                if ($request->has('parent_id')) {
                    $parentId = $request->parent_id;
                    if ($parentId === 'null' || $parentId == 0 || $parentId === '') {
                        $query->whereNull('parent_id');
                    } else {
                        $query->where('parent_id', $parentId);
                    }
                }

                return $query->orderByRaw('sort_order IS NULL, sort_order ASC')
                    ->orderBy('id', 'desc')
                    ->withTrashed()
                    ->get();
            });

            return response()->json(['success' => true, 'data' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function getTree(): JsonResponse
    {
        try {
            $categories = Cache::remember('categories_tree_all', 86400, function () {
                return Category::whereNull('parent_id')
                    ->with(['children' => function ($q) {
                        $q->orderByRaw('sort_order IS NULL, sort_order ASC')->orderBy('id', 'desc');
                    }])
                    ->orderByRaw('sort_order IS NULL, sort_order ASC')
                    ->orderBy('id', 'desc')
                    ->get();
            });

            return response()->json(['success' => true, 'data' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải cây danh mục: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['slug'] = $this->generateUniqueSlug($data['name']);
            $data['status'] = $data['status'] ?? 'active';

            $data['sort_order'] = ($data['status'] === 'active') ? $this->getNextSortOrder() : null;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $request->file('thumbnail')->store('categories/thumbnails', 'public');
            }
            if ($request->hasFile('size_guide_image')) {
                $data['size_guide_image'] = $request->file('size_guide_image')->store('categories/size_guides', 'public');
            }

            $category = Category::create($data);
            $category->load('parent');

            $this->clearCache($category);
            broadcast(new CategoryEvent('created', $category))->toOthers();

            return response()->json(['success' => true, 'message' => 'Tạo danh mục thành công!', 'data' => $category], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $category = Category::withTrashed()->with('parent')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $category]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy danh mục này.'], 404);
        }
    }

    public function update(UpdateCategoryRequest $request, $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $data = $request->validated();

            if (isset($data['parent_id']) && $this->isCircularReference($category->id, $data['parent_id'])) {
                return response()->json(['success' => false, 'message' => 'Lỗi đệ quy: Không thể chọn danh mục con/cháu làm danh mục cha!'], 422);
            }

            if ($data['name'] !== $category->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
            }

            if (isset($data['status']) && $category->status !== $data['status']) {
                $data['sort_order'] = ($data['status'] === 'active') ? $this->getNextSortOrder() : null;
            }

            if ($request->hasFile('thumbnail')) {
                if ($category->thumbnail) Storage::disk('public')->delete($category->thumbnail);
                $data['thumbnail'] = $request->file('thumbnail')->store('categories/thumbnails', 'public');
            } elseif (isset($data['remove_thumbnail']) && filter_var($data['remove_thumbnail'], FILTER_VALIDATE_BOOLEAN)) {
                if ($category->thumbnail) Storage::disk('public')->delete($category->thumbnail);
                $data['thumbnail'] = null;
            }

            if ($request->hasFile('size_guide_image')) {
                if ($category->size_guide_image) Storage::disk('public')->delete($category->size_guide_image);
                $data['size_guide_image'] = $request->file('size_guide_image')->store('categories/size_guides', 'public');
            } elseif (isset($data['remove_size_guide']) && filter_var($data['remove_size_guide'], FILTER_VALIDATE_BOOLEAN)) {
                if ($category->size_guide_image) Storage::disk('public')->delete($category->size_guide_image);
                $data['size_guide_image'] = null;
            }

            $category->update($data);
            $category->load('parent');

            $this->clearCache($category);
            broadcast(new CategoryEvent('updated', $category))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật danh mục thành công!', 'data' => $category]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            if ($category->children()->exists()) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa do danh mục này đang chứa danh mục con!'], 422);
            }
            if ($category->products()->exists()) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa do danh mục này đang chứa sản phẩm!'], 422);
            }

            $category->sort_order = null;
            $category->slug = $category->slug . '-deleted-' . time();
            $category->save();

            $category->delete();

            $this->clearCache($category);
            broadcast(new CategoryEvent('deleted', $category))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã đưa danh mục vào thùng rác!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $category = Category::withTrashed()->findOrFail($id);

            if ($category->parent_id && !Category::where('id', $category->parent_id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Danh mục cha đã bị xóa cứng, không thể khôi phục!'], 422);
            }

            $originalSlug = preg_replace('/-deleted-\d+$/', '', $category->slug);
            if (Category::where('slug', $originalSlug)->whereNull('deleted_at')->exists()) {
                return response()->json(['success' => false, 'message' => 'Tên danh mục này đã bị tài khoản khác lấy mất trong thời gian bị xóa.'], 400);
            }

            $category->slug = $originalSlug;

            if ($category->status === 'active') {
                $category->sort_order = $this->getNextSortOrder();
            }

            $category->save();
            $category->restore();
            $category->load('parent');

            $this->clearCache($category);
            broadcast(new CategoryEvent('restored', $category))->toOthers();

            return response()->json(['success' => true, 'message' => 'Khôi phục danh mục thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->categories as $item) {
                    Category::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
                }
            });
            
            $this->clearCache();

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

        while (Category::where('slug', $slug)->where('id', '!=', $ignoreId)->withTrashed()->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    private function isCircularReference($categoryId, $parentId): bool
    {
        if ($categoryId == $parentId) return true;

        $currentParent = Category::find($parentId);
        while ($currentParent) {
            if ($currentParent->parent_id == $categoryId) return true;
            $currentParent = Category::find($currentParent->parent_id);
        }

        return false;
    }
}