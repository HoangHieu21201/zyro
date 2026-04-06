<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttributeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Lấy tất cả thuộc tính kèm theo các giá trị của nó
            $attributes = Attribute::with('values')->orderBy('id', 'desc')->get();
            return response()->json(['success' => true, 'data' => $attributes]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name'
        ], [
            'name.unique' => 'Tên thuộc tính này đã tồn tại.'
        ]);

        try {
            $attribute = Attribute::create(['name' => $request->name]);
            return response()->json(['success' => true, 'message' => 'Đã thêm thuộc tính', 'data' => $attribute], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $id
        ]);

        try {
            $attribute = Attribute::findOrFail($id);
            $attribute->update(['name' => $request->name]);
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $attribute]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $attribute = Attribute::findOrFail($id);
            // Xóa giá trị con trước
            $attribute->values()->delete();
            $attribute->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa thuộc tính']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}