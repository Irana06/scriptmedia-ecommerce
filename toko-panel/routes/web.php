<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRentalOrderController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransRentalNotificationController;
use App\Http\Controllers\RentalOrderController;
use App\Http\Controllers\TenantPortalController;
use App\Livewire\Admin\Billing\ManageBilling;
use App\Livewire\Admin\ContentRequests\ManageContentRequests;
use App\Livewire\Admin\Plans\ManagePlans;
use App\Livewire\Admin\Tenants\ManageTenants;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::post('payments/midtrans/rental-notification', MidtransRentalNotificationController::class)
    ->middleware('throttle:120,1')
    ->name('payments.midtrans.rental-notification');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardRedirectController::class)->name('dashboard');
    Route::get('mulai/{plan:slug}', [RentalOrderController::class, 'create'])->name('onboarding.create');
    Route::post('mulai/{plan:slug}', [RentalOrderController::class, 'store'])->name('onboarding.store');
    Route::get('portal/orders/{rentalOrder}', [RentalOrderController::class, 'show'])->name('portal.orders.show');
    Route::post('portal/orders/{rentalOrder}/retry', [RentalOrderController::class, 'retry'])->name('portal.orders.retry');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('rental-orders', [AdminRentalOrderController::class, 'index'])->name('rental-orders.index');
        Route::post('rental-orders/{rentalOrder}/provision', [AdminRentalOrderController::class, 'provision'])->name('rental-orders.provision');
        Route::livewire('billing', ManageBilling::class)->name('billing.index');
        Route::livewire('content-requests', ManageContentRequests::class)->name('content-requests.index');
        Route::livewire('plans', ManagePlans::class)->name('plans.index');
        Route::livewire('tenants', ManageTenants::class)->name('tenants.index');
    });

    Route::prefix('portal')->name('portal.')->middleware(['role:owner', 'tenant.owner'])->group(function () {
        Route::get('dashboard', [TenantPortalController::class, 'index'])->name('dashboard');
        Route::get('tenants/{tenant}', [TenantPortalController::class, 'show'])->name('tenants.show');
    });
});

require __DIR__.'/settings.php';
