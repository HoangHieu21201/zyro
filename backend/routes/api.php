<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORT ADMIN CONTROLLERS ĐÃ CÓ ---
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminController;

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

        // 1.1 Auth & Cá nhân
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
        // Route::post('profile', [AdminProfileController::class, 'updateProfile']);

        // 1.2 Phân quyền & Nhân sự (ĐÃ LÀM XONG CONTROLLER)
        Route::post('admins/{id}/restore', [AdminController::class, 'restore']);
        Route::apiResource('admins', AdminController::class);
        
        
    });
});

// 2. CLIENT API ROUTES
Route::prefix('v1/client')->group(function () {

   

});