<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\StorefrontContext;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredProducts = StorefrontContext::scopeProducts(Product::query())
            ->available()
            ->where('is_featured', true)
            ->with(['category', 'media'])
            ->latest()
            ->limit(6)
            ->get();

        $bestSeller = StorefrontContext::scopeProducts(Product::query())
            ->available()
            ->with(['category', 'media'])
            ->withSum('orderItems as units_sold', 'quantity')
            ->orderByDesc('units_sold')
            ->orderByDesc('is_featured')
            ->orderBy('id')
            ->first();

        return view('storefront.home', compact('featuredProducts', 'bestSeller'));
    }
}
