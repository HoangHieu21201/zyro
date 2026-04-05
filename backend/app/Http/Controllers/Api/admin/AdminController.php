<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Danh sách tài khoản (Bao gồm cả tài khoản đã bị khóa/xóa mềm)
     */
    public function index()
    {
        // Dùng withTrashed() để Admin có thể xem được cả những tài khoản đã bị xóa mềm
        $admins = Admin::withTrashed()->with('role')->orderBy('id', 'desc')->get();
        
        return response()->json([
            'success' => true, 
            'data' => $admins
        ]);
    }

    /**
     * Thêm mới tài khoản
     */
    public function store(StoreAdminRequest $request)
    {
        // Lấy dữ liệu đã được Request validate sạch sẽ
        $data = $request->validated();
        
        // Băm mật khẩu
        $data['password'] = Hash::make($data['password']);

        // Xử lý upload ảnh (Lưu vào thư mục avatars/admins)
        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = $request->file('avatar')->store('avatars/admins', 'public');
        }

        $admin = Admin::create($data);

        return response()->json([
            'success' => true, 
            'message' => 'Tạo tài khoản thành công!', 
            'data' => $admin
        ], 201);
    }

    /**
     * Lấy thông tin 1 nhân viên chi tiết
     */
    public function show($id)
    {
        $admin = Admin::withTrashed()->with('role')->findOrFail($id);
        
        return response()->json([
            'success' => true, 
            'data' => $admin
        ]);
    }

    /**
     * Cập nhật thông tin tài khoản
     */
    public function update(UpdateAdminRequest $request, $id)
    {
        $admin = Admin::findOrFail($id);

        // Logic bảo vệ: Người khác không được sửa Super Admin gốc (ID = 1)
        if ($admin->id == 1 && Auth::id() != 1) {
            return response()->json([
                'success' => false, 
                'message' => 'Bạn không có quyền sửa tài khoản Super Admin gốc!'
            ], 403);
        }

        $data = $request->validated();

        // Xử lý mật khẩu (Nếu có nhập mới thì băm, không thì loại bỏ khỏi mảng update)
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Xử lý Avatar (Upload mới hoặc Xóa ảnh cũ)
        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ trên disk nếu tồn tại
            if ($admin->avatar_url && Storage::disk('public')->exists($admin->avatar_url)) {
                Storage::disk('public')->delete($admin->avatar_url);
            }
            $data['avatar_url'] = $request->file('avatar')->store('avatars/admins', 'public');
            
        } elseif ($request->input('remove_avatar') == 'true') {
            // Người dùng chủ động bấm nút "Xóa ảnh đại diện"
            if ($admin->avatar_url && Storage::disk('public')->exists($admin->avatar_url)) {
                Storage::disk('public')->delete($admin->avatar_url);
            }
            $data['avatar_url'] = null;
        }

        $admin->update($data);

        return response()->json([
            'success' => true, 
            'message' => 'Cập nhật tài khoản thành công!'
        ]);
    }

    /**
     * Xóa tài khoản (Xóa mềm - Soft Delete)
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);

        // Bảo vệ Super Admin gốc
        if ($admin->id == 1) {
            return response()->json([
                'success' => false, 
                'message' => 'Không thể xóa Super Admin gốc của hệ thống!'
            ], 403);
        }

        // Chặn người dùng tự xóa chính tài khoản mình đang đăng nhập
        if ($admin->id == Auth::id()) {
            return response()->json([
                'success' => false, 
                'message' => 'Bạn không thể tự khóa/xóa chính mình!'
            ], 400);
        }

        $admin->delete();
        
        return response()->json([
            'success' => true, 
            'message' => 'Đã chuyển tài khoản vào thùng rác!'
        ]);
    }

    /**
     * Khôi phục tài khoản từ Thùng rác (Restore)
     */
    public function restore($id)
    {
        // Phải dùng withTrashed() mới tìm ra được đứa đã bị xóa mềm
        $admin = Admin::withTrashed()->findOrFail($id);
        $admin->restore();

        return response()->json([
            'success' => true, 
            'message' => 'Đã khôi phục tài khoản nhân viên thành công!'
        ]);
    }
}