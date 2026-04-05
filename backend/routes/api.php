<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ModuleController;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

// ==========================================
// 1. ADMIN API ROUTES
// ==========================================
Route::prefix('v1/admin')->group(function () {

    // --- AUTH ROUTES
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AdminAuthController::class, 'resetPassword']);

    // --- PROTECTED ROUTES (Cần Bearer Token) ---
    Route::middleware('auth:sanctum')->group(function () {

        // 1.1 Auth & Cá nhân (Mọi người đều được phép)
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::post('profile/info', [AdminProfileController::class, 'updateInfo']);
        Route::put('profile/password', [AdminProfileController::class, 'updatePassword']);

        // 1.2 Quản trị viên
        Route::middleware(['check.module:admin_staff'])->group(function () {
            Route::post('admins/{id}/restore', [AdminController::class, 'restore']);
            Route::apiResource('admins', AdminController::class);
        });

        // 1.3 Phân quyền (Roles)
        Route::middleware(['check.module:admin_roles'])->group(function () {
            Route::post('roles/{id}/restore', [RoleController::class, 'restore']);
            Route::apiResource('roles', RoleController::class);

            // Quản lý Module nằm chung trong module_code: admin_roles
            Route::get('modules', [ModuleController::class, 'index']);
            Route::post('modules/sync', [ModuleController::class, 'sync']);
            Route::put('modules/{id}/level', [ModuleController::class, 'updateLevel']);
        });

        // 1.4 Danh mục sản phẩm (Categories)
        Route::middleware(['check.module:admin_categories'])->group(function () {
            Route::post('categories/{id}/restore', [CategoryController::class, 'restore']);
            Route::post('categories/reorder', [CategoryController::class, 'reorder']);
            Route::apiResource('categories', CategoryController::class);
        });
    });
});

// ==========================================
// 2. CLIENT API ROUTES
// ==========================================
Route::prefix('v1/client')->group(function () {
    // Các route của app User
});
