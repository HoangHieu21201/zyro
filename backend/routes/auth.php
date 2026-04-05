<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\admin\AdminAuthController;
use App\Http\Controllers\Api\admin\AdminForgotPasswordController;


Route::prefix('admin')->group(function () {
    Route::post('/register', [AdminAuthController::class, 'store']);
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::post('/forgot-password', [AdminForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [AdminForgotPasswordController::class, 'resetPassword']);
});
