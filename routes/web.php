<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfiguratorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\BuildCatalogController;

// Main configurator page
Route::get('/', [ConfiguratorController::class, 'index'])->name('configurator');

// Builds catalog pages
Route::get('/builds', [BuildCatalogController::class, 'index'])->name('builds.index');
Route::get('/builds/{id}', [BuildCatalogController::class, 'show'])->name('builds.show');

// Builds catalog API (public)
Route::get('/api/builds', [BuildCatalogController::class, 'apiIndex']);

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Current user API
Route::get('/api/user', [AuthController::class, 'user']);

// Products API
Route::get('/api/products/{category}', [ProductController::class, 'byCategory']);
Route::get('/api/products/{category}/filters', [ProductController::class, 'filters']);
Route::get('/api/product/{productId}', [ProductController::class, 'show']);

// Templates API
Route::get('/api/templates', [BuildController::class, 'templates']);

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Build / Cart
    Route::post('/api/build/add-to-cart', [BuildController::class, 'addToCart']);
    Route::post('/api/build/export', [BuildController::class, 'exportBuild']);

    // Templates (user can view, privileged can manage)
    Route::post('/api/templates', [BuildController::class, 'storeTemplate']);
    Route::delete('/api/templates/{templateId}', [BuildController::class, 'destroyTemplate']);

    // Published builds
    Route::post('/api/builds', [BuildCatalogController::class, 'store']);
    Route::post('/api/builds/{id}/vote', [BuildCatalogController::class, 'vote']);
    Route::post('/api/builds/{id}/comment', [BuildCatalogController::class, 'comment']);
});

// Privileged routes (admin, content_manager, warehouse_manager)
Route::middleware(['auth', \App\Http\Middleware\PrivilegedMiddleware::class])->group(function () {
    // Product CRUD
    Route::post('/api/products', [ProductController::class, 'store']);
    Route::put('/api/product/{productId}', [ProductController::class, 'update']);
    Route::delete('/api/product/{productId}', [ProductController::class, 'destroy']);

    // Product image upload
    Route::post('/api/product/upload-image', [ProductController::class, 'uploadImage']);

    // Template management for privileged
    Route::put('/api/templates/{templateId}', [BuildController::class, 'updateTemplate']);

    // Build catalog moderation
    Route::delete('/api/builds/{id}', [BuildCatalogController::class, 'destroy']);
    Route::delete('/api/builds/comments/{id}', [BuildCatalogController::class, 'destroyComment']);
});

// Admin panel
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::get('/api/admin/users', [AdminController::class, 'users']);
    Route::put('/api/admin/users/{id}/role', [AdminController::class, 'updateRole']);
    Route::delete('/api/admin/users/{id}', [AdminController::class, 'destroyUser']);
});
