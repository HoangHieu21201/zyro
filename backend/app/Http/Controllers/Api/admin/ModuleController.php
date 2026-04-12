<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModulePermission;
use App\Http\Requests\Module\UpdateModuleLevelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Events\ModuleEvent;

class ModuleController extends Controller
{
    private string $cacheKey = 'admin_system_modules';

    private function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    // Lấy danh sách cấu hình trang (Modules)
    public function index(): JsonResponse
    {
        try {
            $modules = Cache::remember($this->cacheKey, 86400, function () {
                return ModulePermission::orderBy('id', 'asc')->get();
            });
            return response()->json(['success' => true, 'data' => $modules]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // Đồng bộ file config/modules.php vào Database
    public function sync(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (($user?->role->level ?? 999) !== 1) {
                return response()->json(['success' => false, 'message' => 'Chỉ Super Admin mới có quyền đồng bộ hệ thống!'], 403);
            }

            $configModules = config('modules', []);

            if (empty($configModules)) {
                return response()->json(['success' => false, 'message' => 'File config/modules.php đang trống.'], 400);
            }

            $syncedCount = 0;
            $hasChanges = false;

            foreach ($configModules as $mod) {
                $existingModule = ModulePermission::where('module_code', $mod['module_code'])->first();
                
                if ($existingModule) {
                    if ($existingModule->module_name !== $mod['module_name']) {
                        $existingModule->update(['module_name' => $mod['module_name']]);
                        $hasChanges = true;
                    }
                } else {
                    ModulePermission::create([
                        'module_code'    => $mod['module_code'],
                        'module_name'    => $mod['module_name'],
                        'required_level' => $mod['default_level']
                    ]);
                    $syncedCount++;
                    $hasChanges = true;
                }
            }

            if ($hasChanges) {
                $this->clearCache();
            }

            return response()->json([
                'success' => true, 
                'message' => "Đồng bộ hoàn tất. Đã thêm mới {$syncedCount} chức năng."
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // Cập nhật cấp độ yêu cầu
    public function updateLevel(UpdateModuleLevelRequest $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (($user?->role->level ?? 999) !== 1) {
                return response()->json(['success' => false, 'message' => 'Từ chối truy cập. Dành riêng cho Super Admin!'], 403);
            }

            $module = ModulePermission::findOrFail($id);
            $module->update(['required_level' => $request->validated()['required_level']]);

            $this->clearCache();
            broadcast(new ModuleEvent('updated', $module))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật cấp độ thành công', 'data' => $module]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}