<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromUnwantedDomains::class,
])->group(function (): void {
    Route::get('/_tenant/health', function () {
        $currentTenant = tenant();

        return response()->json([
            'tenant_id' => $currentTenant?->getTenantKey(),
            'database' => config('database.connections.tenant.database'),
            'provisioning_status' => $currentTenant instanceof Tenant ? $currentTenant->provisioning_status : null,
            'store_status' => $currentTenant instanceof Tenant ? $currentTenant->store_status : null,
        ]);
    })->name('tenant.health');
});
