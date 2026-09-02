<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseDemoStore
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('demoStore');
        abort_unless(is_string($slug) && array_key_exists($slug, config('demo-stores')), 404);

        $request->attributes->set('demo_store_slug', $slug);
        $request->route()?->forgetParameter('demoStore');

        return $next($request);
    }
}
