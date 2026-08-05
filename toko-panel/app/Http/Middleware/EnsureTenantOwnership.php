<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOwnership
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        $tenant = $request->route('tenant');

        if ($tenant instanceof Tenant) {
            abort_unless(
                $tenant->owner_user_id === $user->getKey(),
                Response::HTTP_FORBIDDEN,
            );
        } else {
            abort_unless($user->ownedTenants()->exists(), Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
