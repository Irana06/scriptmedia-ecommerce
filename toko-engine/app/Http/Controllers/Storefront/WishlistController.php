<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WishlistService;
use App\Support\StorefrontContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WishlistController extends Controller
{
    public function index(WishlistService $wishlist): View
    {
        $this->ensureProDemo();

        return view('storefront.wishlist.index', ['products' => $wishlist->products()]);
    }

    public function store(Product $product, WishlistService $wishlist): RedirectResponse
    {
        $this->ensureProProduct($product);
        $wishlist->add($product);

        return back()->with('success', 'Produk disimpan ke wishlist.');
    }

    public function destroy(Product $product, WishlistService $wishlist): RedirectResponse
    {
        $this->ensureProProduct($product);
        $wishlist->remove($product);

        return back()->with('success', 'Produk dihapus dari wishlist.');
    }

    private function ensureProDemo(): void
    {
        abort_unless(StorefrontContext::slug() === 'pro', 404);
    }

    private function ensureProProduct(Product $product): void
    {
        $this->ensureProDemo();
        abort_unless($product->is_active && str_starts_with($product->slug, 'pro-'), 404);
    }
}
