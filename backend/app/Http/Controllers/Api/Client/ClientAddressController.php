<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAddressController extends Controller
{
    /**
     * Lấy danh sách địa chỉ của User hiện tại
     */
    public function index()
    {
        $addresses = UserAddress::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('id', 'desc')
            ->get();
            
        return response()->json(['success' => true, 'data' => $addresses]);
    }

    /**
     * Thêm địa chỉ mới
     */
    public function store(Request $request)
    {
        // ĐÃ FIX LỖI 500: Chuyển Validation rules thành dạng Mảng (Array) 
        // để dấu "|" trong Regex không làm sập chức năng phân tích của Laravel.
        $validated = $request->validate([
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_phone'   => ['required', 'regex:/^(0|\+84)[35789][0-9]{8}$/'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'city'             => ['required', 'string', 'max:100'],
            'district'         => ['required', 'string', 'max:100'],
            'ward'             => ['required', 'string', 'max:100'],
            'is_default'       => ['nullable', 'boolean']
        ], [
            'customer_phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.'
        ]);

        $userId = Auth::id();
        $isFirst = UserAddress::where('user_id', $userId)->count() === 0;

        // Xử lý cờ boolean an toàn cho PHP 8
        $isDefault = $request->boolean('is_default');

        // Nếu là địa chỉ đầu tiên hoặc được tick chọn mặc định, gỡ mặc định các địa chỉ cũ
        if ($isFirst || $isDefault) {
            UserAddress::where('user_id', $userId)->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = false;
        }

        $validated['user_id'] = $userId;
        $address = UserAddress::create($validated);

        return response()->json(['success' => true, 'message' => 'Đã thêm địa chỉ mới.', 'data' => $address], 201);
    }

    /**
     * Cập nhật địa chỉ (BẢO VỆ BỞI IDOR)
     */
    public function update(Request $request, $id)
    {
        // ĐÃ FIX LỖI 500: Tương tự như hàm store
        $validated = $request->validate([
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_phone'   => ['required', 'regex:/^(0|\+84)[35789][0-9]{8}$/'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'city'             => ['required', 'string', 'max:100'],
            'district'         => ['required', 'string', 'max:100'],
            'ward'             => ['required', 'string', 'max:100'],
            'is_default'       => ['nullable', 'boolean']
        ], [
            'customer_phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.'
        ]);

        // CHỐT CHẶN IDOR: Chỉ tìm trong danh sách địa chỉ của chính User này
        $address = UserAddress::where('user_id', Auth::id())->findOrFail($id);

        $isDefault = $request->boolean('is_default');

        if ($isDefault && !$address->is_default) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            // Không cho phép bỏ mặc định nếu nó đang là mặc định (phải chọn cái khác làm mặc định trước)
            unset($validated['is_default']); 
        }

        $address->update($validated);

        return response()->json(['success' => true, 'message' => 'Đã cập nhật địa chỉ.', 'data' => $address]);
    }

    /**
     * Xóa địa chỉ (BẢO VỆ BỞI IDOR)
     */
    public function destroy($id)
    {
        // CHỐT CHẶN IDOR
        $address = UserAddress::where('user_id', Auth::id())->findOrFail($id);
        
        $wasDefault = $address->is_default;
        $address->delete();

        // Nếu xóa trúng địa chỉ mặc định, tự động gán địa chỉ mới nhất làm mặc định
        if ($wasDefault) {
            $newDefault = UserAddress::where('user_id', Auth::id())->orderBy('id', 'desc')->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Đã xóa địa chỉ.']);
    }

    /**
     * Set địa chỉ làm mặc định (BẢO VỆ BỞI IDOR)
     */
    public function setDefault($id)
    {
        $userId = Auth::id();
        
        // CHỐT CHẶN IDOR
        $address = UserAddress::where('user_id', $userId)->findOrFail($id);

        UserAddress::where('user_id', $userId)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['success' => true, 'message' => 'Đã đặt làm địa chỉ mặc định.']);
    }
}