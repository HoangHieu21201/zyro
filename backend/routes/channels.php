<?php


use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.roles', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.admins', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.categories', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.modules', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.brands', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.users', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.tiers', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.products', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.lookbooks', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.vouchers', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.banners', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.orders', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.wishlists', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.reviews', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.orders', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.flash_sales', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admin.inventory', function ($user) {
    return $user !== null;
}, ['guards' => ['sanctum']]);