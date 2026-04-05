<?php

// File: backend/app/Http/Controllers/Api/Admin/CategoryController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->withTrashed()
            ->paginate(20);
            
        return response()->json($categories);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('categories/thumbnails', 'public');
        }
        
        if ($request->hasFile('size_guide_image')) {
            $data['size_guide_image'] = $request->file('size_guide_image')->store('categories/size_guides', 'public');
        }

        $category = Category::create($data);
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validated();

        if (isset($data['parent_id']) && $this->isCircularReference($category->id, $data['parent_id'])) {
            return response()->json(['message' => 'Lỗi vòng lặp: Không thể chọn danh mục con làm danh mục cha.'], 422);
        }

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
        }

        if ($request->hasFile('thumbnail')) {
            if ($category->thumbnail) Storage::disk('public')->delete($category->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('categories/thumbnails', 'public');
        }

        if ($request->hasFile('size_guide_image')) {
            if ($category->size_guide_image) Storage::disk('public')->delete($category->size_guide_image);
            $data['size_guide_image'] = $request->file('size_guide_image')->store('categories/size_guides', 'public');
        }

        $category->update($data);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->children()->exists() || $category->products()->exists()) {
            return response()->json(['message' => 'Không thể xóa danh mục đang chứa danh mục con hoặc sản phẩm.'], 422);
        }

        $category->delete();
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();
        return response()->json($category);
    }

    private function generateUniqueSlug($name, $ignoreId = null)
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

    private function isCircularReference($categoryId, $parentId)
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