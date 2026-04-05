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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Hàm nội bộ: Lấy số thứ tự hiển thị tiếp theo
     */
    private function getNextSortOrder(): int
    {
        $max = Category::max('sort_order');
        return is_numeric($max) ? $max + 1 : 1;
    }

    public function index(): JsonResponse
    {
        try {
            $categories = Category::with('parent')->withCount('children')
                // Ưu tiên hiện các category có số trước, null đẩy xuống cuối
                ->orderByRaw('sort_order IS NULL, sort_order ASC')
                ->orderBy('id', 'desc')
                ->withTrashed()
                ->get();
                
            return response()->json(['success' => true, 'data' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['slug'] = $this->generateUniqueSlug($data['name']);
            $data['status'] = $data['status'] ?? 'active';
            
            // LOGIC SẮP XẾP: Tự động cấp phát nếu đang active
            $data['sort_order'] = ($data['status'] === 'active') ? $this->getNextSortOrder() : null;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $request->file('thumbnail')->store('categories/thumbnails', 'public');
            }
            if ($request->hasFile('size_guide_image')) {
                $data['size_guide_image'] = $request->file('size_guide_image')->store('categories/size_guides', 'public');
            }

            $category = Category::create($data);
            $category->load('parent');

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

            // LOGIC SẮP XẾP: Cập nhật lại khi thay đổi trạng thái Active/Hidden
            if (isset($data['status']) && $category->status !== $data['status']) {
                $data['sort_order'] = ($data['status'] === 'active') ? $this->getNextSortOrder() : null;
            }

            // Quản lý Thumbnail & Size Guide
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

            // LOGIC SẮP XẾP: Xóa mềm thì tước bỏ vị trí ưu tiên
            $category->sort_order = null;
            
            // Xóa slug để nhường chỗ cho danh mục khác
            $category->slug = $category->slug . '-deleted-' . time();
            $category->save();

            $category->delete();

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

            // LOGIC SẮP XẾP: Cấp phát lại vị trí mới nhất
            if ($category->status === 'active') {
                $category->sort_order = $this->getNextSortOrder();
            }

            $category->save();
            $category->restore();
            $category->load('parent');

            broadcast(new CategoryEvent('restored', $category))->toOthers();

            return response()->json(['success' => true, 'message' => 'Khôi phục danh mục thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    /**
     * CẬP NHẬT TỪ KÉO THẢ (DRAG & DROP)
     */
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
            return response()->json(['success' => true, 'message' => 'Cập nhật thứ tự hiển thị thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Sinh Slug không trùng lặp
     */
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

    /**
     * Helper: Kiểm tra tham chiếu vòng (Ngăn cản Cha chọn Con làm Cha của nó)
     */
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