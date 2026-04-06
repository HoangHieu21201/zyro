<?php

// File: backend/app/Http/Controllers/Api/Admin/UserAddressController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    /**
     * Thêm địa chỉ mới cho User
     */
    public function store(Request $request, $userId): JsonResponse
    {
        $request->validate([
            'customer_name'    => 'required|string|max:100',
            'customer_phone'   => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'city'             => 'required|string|max:100',
            'district'         => 'required|string|max:100',
            'ward'             => 'required|string|max:100',
            'is_default'       => 'boolean'
        ]);

        try {
            $user = User::findOrFail($userId);

            DB::transaction(function () use ($request, $user) {
                $isDefault = $request->boolean('is_default');

                // Nếu chọn làm mặc định, gỡ mặc định của các địa chỉ cũ
                if ($isDefault) {
                    $user->addresses()->update(['is_default' => false]);
                }

                // Nếu user chưa có địa chỉ nào, bắt buộc địa chỉ đầu tiên là mặc định
                if ($user->addresses()->count() === 0) {
                    $isDefault = true;
                }

                $user->addresses()->create(array_merge($request->all(), ['is_default' => $isDefault]));
            });

            return response()->json(['success' => true, 'message' => 'Thêm địa chỉ thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật địa chỉ
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'customer_name'    => 'required|string|max:100',
            'customer_phone'   => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'city'             => 'required|string|max:100',
            'district'         => 'required|string|max:100',
            'ward'             => 'required|string|max:100',
            'is_default'       => 'boolean'
        ]);

        try {
            $address = UserAddress::findOrFail($id);

            DB::transaction(function () use ($request, $address) {
                $isDefault = $request->boolean('is_default');

                if ($isDefault && !$address->is_default) {
                    UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
                }

                // Không cho bỏ mặc định nếu nó đang là mặc định (Phải set cái khác lên làm mặc định thay thế)
                if (!$isDefault && $address->is_default) {
                    $isDefault = true; 
                }

                $address->update(array_merge($request->all(), ['is_default' => $isDefault]));
            });

            return response()->json(['success' => true, 'message' => 'Cập nhật địa chỉ thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Đặt làm mặc định nhanh
     */
    public function setDefault($id): JsonResponse
    {
        try {
            $address = UserAddress::findOrFail($id);

            DB::transaction(function () use ($address) {
                UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
                $address->update(['is_default' => true]);
            });

            return response()->json(['success' => true, 'message' => 'Đã thay đổi địa chỉ mặc định!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xóa địa chỉ
     */
    public function destroy($id): JsonResponse
    {
        try {
            $address = UserAddress::findOrFail($id);
            
            if ($address->is_default) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa địa chỉ đang được đặt làm Mặc định!'], 400);
            }

            $address->delete();

            return response()->json(['success' => true, 'message' => 'Đã xóa địa chỉ thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}