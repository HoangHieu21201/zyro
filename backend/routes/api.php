<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

// --- Admin Controllers ---
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\ModuleController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\AttributeController;
use App\Http\Controllers\Api\Admin\AttributeValueController;
use App\Http\Controllers\Api\Admin\LookbookController;
use App\Http\Controllers\Api\Admin\VoucherController;
use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\ReviewController;
use App\Http\Controllers\Api\Admin\WishlistController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\OrderTrackingController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\UserAddressController;
use App\Http\Controllers\Api\Admin\MembershipTierController;
use App\Http\Controllers\Api\Admin\FlashSaleController;

// --- Client Controllers ---
use App\Http\Controllers\Api\Client\HomeController;

// Khởi tạo các kênh Socket nội bộ (Sanctum Auth)
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

        // 1.1 Auth & Cá nhân (Mọi Quản trị viên đều được phép)
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

            // Quản lý Module nằm chung trong quyền admin_roles
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

            // Sổ địa chỉ của từng User
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
            Route::post('products/{id}/restore', [ProductController::class, 'restore']);
            Route::patch('products/{id}/status', [ProductController::class, 'updateStatus']);
            Route::apiResource('products', ProductController::class);

            // Thuộc tính biến thể
            Route::apiResource('attributes', AttributeController::class)->except(['show']);
            Route::post('attribute-values', [AttributeValueController::class, 'store']);
            Route::delete('attribute-values/{id}', [AttributeValueController::class, 'destroy']);
        });

        // 1.9 Quản lý Lookbooks
        Route::middleware(['check.module:admin_lookbooks'])->group(function () {
            Route::post('lookbooks/{id}/restore', [LookbookController::class, 'restore']);
            Route::patch('lookbooks/{id}/status', [LookbookController::class, 'updateStatus']);
            Route::apiResource('lookbooks', LookbookController::class);
        });

        // 1.10 Quản lý Mã khuyến mãi (Vouchers)
        Route::middleware(['check.module:admin_vouchers'])->group(function () {
            Route::post('vouchers/{id}/restore', [VoucherController::class, 'restore']);
            Route::patch('vouchers/{id}/status', [VoucherController::class, 'updateStatus']);
            Route::apiResource('vouchers', VoucherController::class);
        });

        // 1.11 Quản lý Banners
        Route::middleware(['check.module:admin_banners'])->group(function () {
            Route::post('banners/{id}/restore', [BannerController::class, 'restore']);
            Route::post('banners/reorder', [BannerController::class, 'reorder']);
            Route::patch('banners/{id}/status', [BannerController::class, 'updateStatus']);
            Route::apiResource('banners', BannerController::class);
        });

        // 1.12 Đánh giá (Reviews)
        Route::middleware(['check.module:admin_reviews'])->group(function () {
            Route::post('reviews/{id}/restore', [ReviewController::class, 'restore']);
            Route::patch('reviews/{id}/status', [ReviewController::class, 'updateStatus']);
            Route::post('reviews/{id}/reply', [ReviewController::class, 'reply']);
            Route::apiResource('reviews', ReviewController::class)->except(['store', 'update']);
        });

        // 1.13 Thống kê Sản phẩm yêu thích (Wishlists)
        Route::middleware(['check.module:admin_wishlists'])->group(function () {
            Route::apiResource('wishlists', WishlistController::class)->only(['index', 'destroy']);
        });

        // 1.14 Đơn hàng (Orders)
        Route::middleware(['check.module:admin_orders'])->group(function () {
            Route::post('orders/{id}/restore', [OrderController::class, 'restore']);
            Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
            Route::post('orders/{id}/refund', [OrderController::class, 'processRefund']);
            Route::get('orders/{id}/simulation', [OrderTrackingController::class, 'getSimulationData']);
            Route::apiResource('orders', OrderController::class)->except(['store']);
        });

        // 1.15 Flash Sales
        Route::middleware(['check.module:admin_flash_sales'])->group(function () {
            Route::patch('flash_sales/{id}/status', [\App\Http\Controllers\Api\Admin\FlashSaleController::class, 'updateStatus']);
            Route::apiResource('flash_sales', \App\Http\Controllers\Api\Admin\FlashSaleController::class);
        });
    });
});

// ==========================================
// 2. CLIENT API ROUTES (Người dùng)
// ==========================================
Route::prefix('v1/client')->group(function () {

    // Các Data chung (Không cần Login)
    Route::get('home', [HomeController::class, 'index']);

    // Các Route giỏ hàng, đặt hàng, user profile sếp sẽ triển khai thêm tại đây...
    Route::get('/home/new-arrivals-tab', [HomeController::class, 'getNewArrivalsByCategory']);

});
