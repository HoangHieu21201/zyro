<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAddressController extends Controller
{
    public function index()
    {
        $addresses = UserAddress::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('id', 'desc')
            ->get();
            
        return response()->json(['success' => true, 'data' => $addresses]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'ward' => 'required|string',
            'is_default' => 'boolean'
        ]);

        $userId = Auth::id();
        $isFirst = UserAddress::where('user_id', $userId)->count() === 0;

        // Nếu là địa chỉ đầu tiên hoặc được tick chọn mặc định
        if ($isFirst || !empty($validated['is_default'])) {
            UserAddress::where('user_id', $userId)->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = false;
        }

        $validated['user_id'] = $userId;
        $address = UserAddress::create($validated);

        return response()->json(['success' => true, 'message' => 'Đã thêm địa chỉ mới.', 'data' => $address]);
    }

    public function update(Request $request, $id)
    {
        $address = UserAddress::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'ward' => 'required|string',
            'is_default' => 'boolean'
        ]);

        if (!empty($validated['is_default']) && !$address->is_default) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            // Không cho phép bỏ mặc định nếu nó đang là mặc định
            unset($validated['is_default']); 
        }

        $address->update($validated);

        return response()->json(['success' => true, 'message' => 'Đã cập nhật địa chỉ.', 'data' => $address]);
    }

    public function destroy($id)
    {
        $address = UserAddress::where('user_id', Auth::id())->findOrFail($id);
        
        $wasDefault = $address->is_default;
        $address->delete();

        // Nếu xóa địa chỉ mặc định, tự động gán địa chỉ mới nhất làm mặc định
        if ($wasDefault) {
            $newDefault = UserAddress::where('user_id', Auth::id())->orderBy('id', 'desc')->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Đã xóa địa chỉ.']);
    }

    public function setDefault($id)
    {
        $userId = Auth::id();
        $address = UserAddress::where('user_id', $userId)->findOrFail($id);

        UserAddress::where('user_id', $userId)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['success' => true, 'message' => 'Đã đặt làm địa chỉ mặc định.']);
    }
}