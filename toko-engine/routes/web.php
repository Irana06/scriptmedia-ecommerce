<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StoreSettingController as AdminStoreSettingController;
use App\Http\Controllers\Payments\MidtransNotificationController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('cart', [CartController::class, 'index'])->name('cart.index');
Route::post('cart/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('orders/{order}/success', [CheckoutController::class, 'success'])
    ->middleware('signed')
    ->name('checkout.success');
Route::get('orders/track/{token}', [CheckoutController::class, 'track'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:30,1')
    ->name('orders.track');
Route::post('orders/{order}/payments/midtrans/retry', [CheckoutController::class, 'retryMidtrans'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('checkout.midtrans.retry');
Route::post('payments/midtrans/notification', MidtransNotificationController::class)
    ->withoutMiddleware(PreventRequestForgery::class)
    ->middleware('throttle:60,1')
    ->name('payments.midtrans.notification');

Route::get('dashboard', AdminDashboardController::class)
    ->middleware(['auth', 'verified', 'password.changed', 'permission:access admin'])
    ->name('dashboard');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'password.changed', 'permission:access admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('products', AdminProductController::class)
        ->except('show')
        ->middleware('permission:manage products');
    Route::get('orders', [AdminOrderController::class, 'index'])
        ->middleware('permission:manage orders')
        ->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])
        ->middleware('permission:manage orders')
        ->name('orders.show');
    Route::patch('orders/{order}', [AdminOrderController::class, 'update'])
        ->middleware('permission:manage orders')
        ->name('orders.update');
    Route::get('store-settings', [AdminStoreSettingController::class, 'edit'])
        ->middleware('permission:manage store settings')
        ->name('store-settings.edit');
    Route::put('store-settings', [AdminStoreSettingController::class, 'update'])
        ->middleware('permission:manage store settings')
        ->name('store-settings.update');
    Route::patch('payment-gateways/{paymentGateway}', [AdminStoreSettingController::class, 'updateGateway'])
        ->middleware('permission:manage store settings')
        ->name('payment-gateways.update');
    Route::get('reports', AdminReportController::class)
        ->middleware('permission:view reports')
        ->name('reports.index');
});

require __DIR__.'/settings.php';
