<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->toString();
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $products = Product::query()
            ->available()
            ->with(['category', 'media'])
            ->when($categorySlug, fn ($query) => $query->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug),
            ))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('storefront.products.index', compact('categories', 'products', 'categorySlug'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load(['category', 'media']);

        $relatedProducts = Product::query()
            ->available()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['category', 'media'])
            ->limit(3)
            ->get();

        return view('storefront.products.show', compact('product', 'relatedProducts'));
    }
}
