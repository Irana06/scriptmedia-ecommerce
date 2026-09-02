<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DemoStoreController extends Controller
{
    public function __invoke(string $demoStore): View
    {
        $store = config("demo-stores.{$demoStore}");
        abort_unless(is_array($store), 404);

        return view('storefront.demo', [
            'slug' => $demoStore,
            'store' => $store,
            'allStores' => config('demo-stores'),
        ]);
    }
}
