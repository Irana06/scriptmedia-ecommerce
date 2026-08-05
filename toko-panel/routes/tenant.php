<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromUnwantedDomains::class,
])->group(function (): void {
    Route::get('/_tenant/health', function () {
        return response()->json([
            'tenant_id' => tenant()?->getTenantKey(),
            'database' => config('database.connections.tenant.database'),
            'status' => 'active',
        ]);
    })->name('tenant.health');
});
