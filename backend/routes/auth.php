<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes (API)
|--------------------------------------------------------------------------
| Toàn bộ các route liên quan đến xác thực người dùng được tách riêng
| ra đây để dễ bảo trì và mở rộng sau này (Quên mật khẩu, Xác thực Email...)
|
*/

Route::prefix('v1/client')->group(function () {
    
    // Các route không cần đăng nhập
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Các route yêu cầu phải có Token (Đã đăng nhập)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
    
});