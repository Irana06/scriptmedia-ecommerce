<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\TenantPortalController;
use App\Livewire\Admin\Plans\ManagePlans;
use App\Livewire\Admin\Tenants\ManageTenants;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
        Route::livewire('plans', ManagePlans::class)->name('plans.index');
        Route::livewire('tenants', ManageTenants::class)->name('tenants.index');
    });

    Route::prefix('portal')->name('portal.')->middleware(['role:owner', 'tenant.owner'])->group(function () {
        Route::get('dashboard', [TenantPortalController::class, 'index'])->name('dashboard');
        Route::get('tenants/{tenant}', [TenantPortalController::class, 'show'])->name('tenants.show');
        Route::post('tenants/{tenant}/content-requests', [TenantPortalController::class, 'storeContentRequest'])
            ->name('tenants.content-requests.store');
    });
});

require __DIR__.'/settings.php';
