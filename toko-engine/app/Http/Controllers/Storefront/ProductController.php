<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\StorefrontContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->toString();
        $search = StorefrontContext::allows('catalog_search') ? $request->string('q')->trim()->toString() : '';
        $sort = StorefrontContext::allows('catalog_sort') ? $request->string('sort', 'latest')->toString() : 'latest';
        $sort = in_array($sort, ['latest', 'price-low', 'price-high', 'name'], true) ? $sort : 'latest';
        $categories = StorefrontContext::scopeCategories(Category::query())->where('is_active', true)->orderBy('name')->get();
        $products = StorefrontContext::scopeProducts(Product::query())
            ->available()
            ->with(['category', 'media'])
            ->when($search, fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->when($categorySlug, fn ($query) => $query->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug),
            ))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->when($sort === 'price-low', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'price-high', fn ($query) => $query->orderByDesc('price'))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->paginate(12)
            ->withQueryString();

        return view('storefront.products.index', compact('categories', 'products', 'categorySlug', 'search', 'sort'));
    }

    public function show(Product $product): View
    {
        $demoSlug = StorefrontContext::slug();
        abort_unless($product->is_active && ($demoSlug === null || str_starts_with($product->slug, $demoSlug.'-')), 404);
        $product->load(['category', 'media']);

        $relatedProducts = StorefrontContext::scopeProducts(Product::query())
            ->available()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['category', 'media'])
            ->when(! StorefrontContext::allows('related_products'), fn ($query) => $query->whereRaw('1 = 0'))
            ->limit(StorefrontContext::slug() === 'pro' ? 4 : 3)
            ->get();

        return view('storefront.products.show', compact('product', 'relatedProducts'));
    }
}
