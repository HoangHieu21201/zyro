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
use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\UserAddressController;
use App\Http\Controllers\Api\Admin\MembershipTierController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\AttributeController;
use App\Http\Controllers\Api\Admin\AttributeValueController;

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

        // 1.5 Thương hiệu (Brands)
        Route::middleware(['check.module:admin_brands'])->group(function () {
            Route::post('brands/{id}/restore', [BrandController::class, 'restore']);
            Route::post('brands/reorder', [BrandController::class, 'reorder']);
            Route::apiResource('brands', BrandController::class);
        });

        // 1.6 Khách hàng (Users)
        Route::middleware(['check.module:admin_users'])->group(function () {
            Route::post('users/{id}/restore', [UserController::class, 'restore']);
            Route::apiResource('users', UserController::class);

            Route::post('users/{id}/addresses', [UserAddressController::class, 'store']);
            Route::put('addresses/{id}', [UserAddressController::class, 'update']);
            Route::delete('addresses/{id}', [UserAddressController::class, 'destroy']);
            Route::put('addresses/{id}/default', [UserAddressController::class, 'setDefault']);
        });

        // 1.7 Hạng thành viên (Membership Tiers)
        Route::middleware(['check.module:admin_tiers'])->group(function () {
            Route::post('tiers/{id}/restore', [MembershipTierController::class, 'restore']);
            Route::apiResource('tiers', MembershipTierController::class);
        });

        // 1.8 Sản phẩm (Products)
        Route::middleware(['check.module:admin_products'])->group(function () {
            Route::post('products/{id}/restore', [\App\Http\Controllers\Api\Admin\ProductController::class, 'restore']);
            Route::patch('products/{id}/status', [\App\Http\Controllers\Api\Admin\ProductController::class, 'updateStatus']); // ĐÃ THÊM API NÀY
            Route::apiResource('products', \App\Http\Controllers\Api\Admin\ProductController::class);

            Route::apiResource('attributes', \App\Http\Controllers\Api\Admin\AttributeController::class)->except(['show']);
            Route::post('attribute-values', [\App\Http\Controllers\Api\Admin\AttributeValueController::class, 'store']);
        });

        // Quản lý Lookbooks
        Route::middleware(['check.module:admin_lookbooks'])->group(function () {
            Route::post('lookbooks/{id}/restore', [\App\Http\Controllers\Api\Admin\LookbookController::class, 'restore']);
            Route::patch('lookbooks/{id}/status', [\App\Http\Controllers\Api\Admin\LookbookController::class, 'updateStatus']);
            Route::apiResource('lookbooks', \App\Http\Controllers\Api\Admin\LookbookController::class);
        });
    });
});

// ==========================================
// 2. CLIENT API ROUTES
// ==========================================
Route::prefix('v1/client')->group(function () {
    // Các route của app User
});
