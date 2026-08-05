<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredProducts = Product::query()
            ->available()
            ->where('is_featured', true)
            ->with(['category', 'media'])
            ->latest()
            ->limit(6)
            ->get();

        return view('storefront.home', compact('featuredProducts'));
    }
}
