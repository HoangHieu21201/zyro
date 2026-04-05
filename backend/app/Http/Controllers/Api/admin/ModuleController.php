<?php

// File: backend/app/Http/Controllers/Api/Admin/ModuleController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModulePermission;
use App\Http\Requests\Module\UpdateModuleLevelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Events\ModuleEvent; // Đừng quên use Event này nhé

class ModuleController extends Controller
{
    /**
     * Lấy danh sách toàn bộ cấu hình trang (Modules)
     */
    public function index(): JsonResponse
    {
        try {
            $modules = ModulePermission::orderBy('id', 'asc')->get();
            return response()->json(['success' => true, 'data' => $modules]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Đồng bộ file config/modules.php vào Database
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\Admin|null $user */
            $user = $request->user();
            
            // Bảo mật: Chỉ người có level = 1 (Super Admin) mới được phép đồng bộ hệ thống
            if (($user?->role->level ?? 999) !== 1) {
                return response()->json(['success' => false, 'message' => 'Chỉ Super Admin mới có quyền đồng bộ hệ thống!'], 403);
            }

            $configModules = config('modules', []);

            if (empty($configModules)) {
                return response()->json(['success' => false, 'message' => 'File config/modules.php đang trống hoặc không tồn tại.'], 400);
            }

            $syncedCount = 0;

            // Xử lý chống ghi đè (Overwrite Trap)
            foreach ($configModules as $mod) {
                $existingModule = ModulePermission::where('module_code', $mod['module_code'])->first();
                
                if ($existingModule) {
                    // Nếu module đã tồn tại trong DB, ta chỉ cập nhật TÊN (nếu code config đổi tên)
                    // TUYỆT ĐỐI KHÔNG cập nhật 'required_level' để giữ nguyên cấu hình Admin đã thiết lập trước đó
                    if ($existingModule->module_name !== $mod['module_name']) {
                        $existingModule->update(['module_name' => $mod['module_name']]);
                    }
                } else {
                    // Nếu là module mới hoàn toàn, tiến hành tạo mới
                    ModulePermission::create([
                        'module_code'    => $mod['module_code'],
                        'module_name'    => $mod['module_name'],
                        'required_level' => $mod['default_level']
                    ]);
                    $syncedCount++;
                }
            }

            return response()->json([
                'success' => true, 
                'message' => "Đồng bộ hoàn tất. Đã phát hiện và thêm mới {$syncedCount} chức năng vào hệ thống."
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật cấp độ yêu cầu cho 1 Module cụ thể
     */
    public function updateLevel(UpdateModuleLevelRequest $request, $id): JsonResponse
    {
        try {
            /** @var \App\Models\Admin|null $user */
            $user = $request->user();
            
            // Bảo mật: Chỉ Super Admin mới được cấu hình cấp độ truy cập
            if (($user?->role->level ?? 999) !== 1) {
                return response()->json(['success' => false, 'message' => 'Từ chối truy cập. Chức năng này dành riêng cho Super Admin!'], 403);
            }

            $module = ModulePermission::findOrFail($id);
            $data = $request->validated();

            $module->update(['required_level' => $data['required_level']]);

            // THÊM DÒNG NÀY: Bắn Real-time
            broadcast(new ModuleEvent('updated', $module))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật cấp độ trang thành công', 'data' => $module]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}