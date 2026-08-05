<?php

namespace App\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyEnded;
use Stancl\Tenancy\Events\TenancyInitialized;
use Stancl\Tenancy\Listeners\BootstrapTenancy;
use Stancl\Tenancy\Listeners\RevertToCentralContext;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains;

class TenancyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(TenancyInitialized::class, BootstrapTenancy::class);
        Event::listen(TenancyEnded::class, RevertToCentralContext::class);

        Route::middleware('tenant')->group(base_path('routes/tenant.php'));

        $kernel = $this->app->make(Kernel::class);
        $kernel->prependToMiddlewarePriority(InitializeTenancyByDomain::class);
        $kernel->prependToMiddlewarePriority(PreventAccessFromUnwantedDomains::class);
    }
}
