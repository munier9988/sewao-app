<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Buyer\CatalogController as BuyerCatalogController;
use App\Http\Controllers\Buyer\FavoriteController as BuyerFavoriteController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Seller\CategoryController as SellerCategoryController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\SettingController as SellerSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('jelajah'));

Route::get('/jelajah', [BuyerCatalogController::class, 'index'])->name('jelajah');

Route::middleware(['auth', 'role:pembeli'])->group(function () {
    Route::get('/pesanan', [BuyerOrderController::class, 'index'])->name('pesanan');
    Route::post('/orders', [BuyerOrderController::class, 'store'])->name('orders.store');

    Route::get('/favorit', [BuyerFavoriteController::class, 'index'])->name('favorit');
    Route::post('/favorites/toggle/{product}', [BuyerFavoriteController::class, 'toggle'])->name('favorites.toggle');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth', 'role:seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
    Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [SellerProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [SellerProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/categories', [SellerCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [SellerCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [SellerCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [SellerCategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/settings', [SellerSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SellerSettingController::class, 'update'])->name('settings.update');
});
