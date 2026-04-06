<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttributeValueController extends Controller
{
    // API lưu giá trị biến thể (VD: Lưu chữ "Đỏ" cho thuộc tính "Màu sắc")
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value'        => 'required|string|max:255'
        ]);

        try {
            // Kiểm tra tránh trùng lặp giá trị trong cùng 1 thuộc tính
            $exists = AttributeValue::where('attribute_id', $request->attribute_id)
                                    ->where('value', $request->value)
                                    ->first();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Giá trị này đã tồn tại trong thuộc tính.'], 422);
            }

            $attrValue = AttributeValue::create($request->only(['attribute_id', 'value', 'meta_value']));
            return response()->json(['success' => true, 'message' => 'Đã thêm giá trị', 'data' => $attrValue], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}