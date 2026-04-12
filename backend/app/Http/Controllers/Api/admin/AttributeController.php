<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AttributeController extends Controller
{
    // Danh sách thuộc tính
    public function index(): JsonResponse
    {
        try {
            $attributes = Cache::remember('attributes_list_all', 86400, function() {
                return Attribute::with('values')->orderBy('id', 'desc')->get();
            });
            return response()->json(['success' => true, 'data' => $attributes]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Thêm thuộc tính mới
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name'
        ], [
            'name.unique' => 'Tên thuộc tính này đã tồn tại.'
        ]);

        try {
            $attribute = Attribute::create(['name' => $request->name]);
            
            Cache::forget('attributes_list_all');
            
            return response()->json(['success' => true, 'message' => 'Đã thêm thuộc tính', 'data' => $attribute], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Cập nhật thuộc tính
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $id
        ]);

        try {
            $attribute = Attribute::findOrFail($id);
            $attribute->update(['name' => $request->name]);
            
            Cache::forget('attributes_list_all');
            
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $attribute]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Xóa thuộc tính và các giá trị con
    public function destroy($id): JsonResponse
    {
        try {
            $attribute = Attribute::findOrFail($id);
            $attribute->values()->delete();
            $attribute->delete();

            Cache::forget('attributes_list_all');

            return response()->json(['success' => true, 'message' => 'Đã xóa thuộc tính']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}