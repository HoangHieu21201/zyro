<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ModulePermission;

class CheckModulePermission
{
    /**
     * So sánh Level của Admin với Required_Level của Trang (Module)
     */
    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        /** @var \App\Models\Admin|null $admin */
        $admin = $request->user();

        // 1. Kiểm tra tồn tại user và role_id
        if (!$admin || !$admin->role_id) {
            return response()->json(['success' => false, 'message' => 'Lỗi xác thực: Không tìm thấy chức vụ của bạn.'], 401);
        }

        // 2. Lấy level qua Eloquent Model (Nhanh và tối ưu hơn DB::table)
        // Nếu không tìm thấy role, mặc định gán level 999 (cấp thấp nhất)
        $adminLevel = (int) ($admin->role->level ?? 999);

        // 3. Super Admin (Level 1) luôn luôn được phép truy cập tất cả
        if ($adminLevel === 1) {
            return $next($request);
        }

        // 4. Tìm kiếm trang (Module) này trong Database xem yêu cầu cấp mấy
        $module = ModulePermission::where('module_code', $moduleCode)->first();

        // Nếu trang này chưa được cấu hình (chưa Sync), từ chối để bảo mật
        if (!$module) {
            return response()->json([
                'success' => false, 
                'message' => "Chức năng [$moduleCode] chưa được hệ thống cấp phép. Vui lòng báo Super Admin đồng bộ hệ thống."
            ], 403);
        }

        $requiredLevel = (int) $module->required_level;

        // 5. CÔNG THỨC VÀNG: Số cấp của Admin phải NHỎ HƠN HOẶC BẰNG số cấp yêu cầu
        if ($adminLevel > $requiredLevel) {
            return response()->json([
                'success' => false, 
                'message' => "Truy cập bị từ chối: Cấp độ của bạn không đủ để thực hiện thao tác này. (Yêu cầu Cấp $requiredLevel, bạn đang ở Cấp $adminLevel)"
            ], 403);
        }

        return $next($request);
    }
}