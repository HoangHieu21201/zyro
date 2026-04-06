<?php


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

// Bảo vệ kênh 'admin.modules'
Broadcast::channel('admin.modules', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

// Bảo vệ kênh 'admin.brands'
Broadcast::channel('admin.brands', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

// Bảo vệ kênh 'admin.users'
Broadcast::channel('admin.users', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

// Bảo vệ kênh 'admin.tiers'
Broadcast::channel('admin.tiers', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

// Bảo vệ kênh 'admin.products'
Broadcast::channel('admin.products', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);


