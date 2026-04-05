<?php

// File: backend/routes/channels.php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Bảo vệ kênh 'admin.roles'
Broadcast::channel('admin.roles', function ($user) {
    return $user !== null; 
}, ['guards' => ['sanctum']]);

// Bảo vệ kênh 'admin.admins'
Broadcast::channel('admin.admins', function ($user) {
    return $user !== null; 
}, ['guards' => ['sanctum']]);

// Bảo vệ kênh 'admin.categories'
Broadcast::channel('admin.categories', function ($user) {
    return $user !== null; 
}, ['guards' => ['sanctum']]);

// THÊM ĐOẠN NÀY: Cấp phép cho kênh 'admin.modules'
Broadcast::channel('admin.modules', function ($user) {
    return $user !== null; 
}, ['guards' => ['sanctum']]);