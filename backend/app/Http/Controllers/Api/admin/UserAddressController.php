<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Events\UserEvent;

class UserAddressController extends Controller
{
    // Dọn dẹp Cache danh sách User vì số lượng địa chỉ (addresses_count) đã thay đổi
    private function clearUserCache(): void
    {
        Cache::forget('users_list_all');
    }

    // Thêm địa chỉ mới
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

                if ($isDefault) {
                    $user->addresses()->update(['is_default' => false]);
                }

                if ($user->addresses()->count() === 0) {
                    $isDefault = true;
                }

                $user->addresses()->create(array_merge($request->all(), ['is_default' => $isDefault]));
            });

            $this->clearUserCache();
            $user->load('tier');
            broadcast(new UserEvent('updated', $user))->toOthers();

            return response()->json(['success' => true, 'message' => 'Thêm địa chỉ thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Cập nhật địa chỉ
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

                if (!$isDefault && $address->is_default) {
                    $isDefault = true; 
                }

                $address->update(array_merge($request->all(), ['is_default' => $isDefault]));
            });

            $this->clearUserCache();
            $user = User::with('tier')->find($address->user_id);
            if ($user) broadcast(new UserEvent('updated', $user))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật địa chỉ thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Đặt làm mặc định
    public function setDefault($id): JsonResponse
    {
        try {
            $address = UserAddress::findOrFail($id);

            DB::transaction(function () use ($address) {
                UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
                $address->update(['is_default' => true]);
            });

            $this->clearUserCache();
            $user = User::with('tier')->find($address->user_id);
            if ($user) broadcast(new UserEvent('updated', $user))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã thay đổi địa chỉ mặc định!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Xóa địa chỉ
    public function destroy($id): JsonResponse
    {
        try {
            $address = UserAddress::findOrFail($id);
            
            if ($address->is_default) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa địa chỉ đang được đặt làm Mặc định!'], 400);
            }

            $userId = $address->user_id;
            $address->delete();

            $this->clearUserCache();
            $user = User::with('tier')->find($userId);
            if ($user) broadcast(new UserEvent('updated', $user))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã xóa địa chỉ thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}