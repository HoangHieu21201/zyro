<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AttributeValueController extends Controller
{
    // Lưu giá trị biến thể
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value'        => 'required|string|max:255'
        ]);

        try {
            $exists = AttributeValue::where('attribute_id', $request->attribute_id)
                                    ->where('value', $request->value)
                                    ->first();
                                    
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Giá trị này đã tồn tại trong thuộc tính.'], 422);
            }

            $attrValue = AttributeValue::create($request->only(['attribute_id', 'value', 'meta_value']));
            
            Cache::forget('attributes_list_all');

            return response()->json(['success' => true, 'message' => 'Đã thêm giá trị', 'data' => $attrValue], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Xóa giá trị
    public function destroy($id): JsonResponse
    {
        try {
            $attrValue = AttributeValue::findOrFail($id);
            $attrValue->delete();
            
            Cache::forget('attributes_list_all');
            
            return response()->json(['success' => true, 'message' => 'Đã dọn dẹp giá trị rác thành công.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa giá trị này: ' . $e->getMessage()], 500);
        }
    }
}