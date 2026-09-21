<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Support\StorefrontContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cart): View
    {
        return view('storefront.cart.index', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'selectedSubtotal' => $cart->selectedSubtotal(),
            'selectedCount' => $cart->selectedCount(),
        ]);
    }

    /** Replaces the whole selection with whatever the shopper ticked. */
    public function select(Request $request, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer'],
            'continue' => ['nullable', 'in:checkout'],
        ]);
        $cart->select($validated['product_ids'] ?? []);

        if (($validated['continue'] ?? null) !== 'checkout') {
            return back();
        }

        if ($cart->selectedCount() === 0) {
            return redirect(StorefrontContext::route('cart.index'))
                ->withErrors(['cart' => 'Pilih dulu produk yang mau dipesan.']);
        }

        return redirect(StorefrontContext::route('checkout.create'));
    }

    public function store(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $validated = $this->addToCart($request, $product, $cart);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    /**
     * Shortcut for shoppers who want to pay straight away. Only this product is
     * ordered; anything already in the cart stays there, just unticked.
     */
    public function buyNow(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $validated = $this->addToCart($request, $product, $cart);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        $cart->selectOnly($product);

        return redirect(StorefrontContext::route('checkout.create'));
    }

    public function update(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $demoSlug = StorefrontContext::slug();
        abort_unless($demoSlug === null || str_starts_with($product->slug, $demoSlug.'-'), 404);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.$product->stock],
        ]);
        $cart->update($product, (int) $validated['quantity']);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Product $product, CartService $cart): RedirectResponse
    {
        $demoSlug = StorefrontContext::slug();
        abort_unless($demoSlug === null || str_starts_with($product->slug, $demoSlug.'-'), 404);
        $cart->remove($product);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    /** Returns a redirect when the product cannot be added, otherwise null. */
    private function addToCart(Request $request, Product $product, CartService $cart): ?RedirectResponse
    {
        $demoSlug = StorefrontContext::slug();
        abort_unless($product->is_active && ($demoSlug === null || str_starts_with($product->slug, $demoSlug.'-')), 404);
        $validated = $request->validate(['quantity' => ['nullable', 'integer', 'min:1']]);

        if ($product->stock < 1) {
            return back()->withErrors(['quantity' => 'Produk sedang habis.']);
        }

        $cart->add($product, (int) ($validated['quantity'] ?? 1));

        return null;
    }
}
