<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
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
            'keys' => ['nullable', 'array'],
            'keys.*' => ['string', 'max:64'],
            'continue' => ['nullable', 'in:checkout'],
        ]);
        $cart->select($validated['keys'] ?? []);

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
        $outcome = $this->addToCart($request, $product, $cart);

        if ($outcome instanceof RedirectResponse) {
            return $outcome;
        }

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    /**
     * Shortcut for shoppers who want to pay straight away. Only this line is
     * ordered; anything already in the cart stays there, just unticked.
     */
    public function buyNow(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $outcome = $this->addToCart($request, $product, $cart);

        if ($outcome instanceof RedirectResponse) {
            return $outcome;
        }

        $cart->selectOnly($product, $outcome);

        return redirect(StorefrontContext::route('checkout.create'));
    }

    public function update(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $this->ensureSellable($product);
        $variant = $this->resolveVariant($request, $product);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.($variant === null ? $product->stock : $variant->stock)],
        ]);
        $cart->update($product, (int) $validated['quantity'], $variant);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $this->ensureInStore($product);
        $cart->remove($product, $this->resolveVariant($request, $product));

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    /**
     * Adds the line and returns the chosen variant, or a redirect when it cannot
     * be added.
     */
    private function addToCart(Request $request, Product $product, CartService $cart): ProductVariant|RedirectResponse|null
    {
        $this->ensureSellable($product);
        $variant = $this->resolveVariant($request, $product);

        if ($product->hasVariants() && $variant === null) {
            return back()->withErrors(['variant' => 'Pilih dulu varian yang kamu mau.']);
        }

        $validated = $request->validate(['quantity' => ['nullable', 'integer', 'min:1']]);

        if (($variant === null ? $product->stock : $variant->stock) < 1) {
            return back()->withErrors(['quantity' => 'Produk sedang habis.']);
        }

        $cart->add($product, (int) ($validated['quantity'] ?? 1), $variant);

        return $variant;
    }

    private function resolveVariant(Request $request, Product $product): ?ProductVariant
    {
        $variantId = $request->input('variant_id');

        if (! is_numeric($variantId)) {
            return null;
        }

        $variant = $product->activeVariants()->firstWhere('id', (int) $variantId);
        abort_unless($variant instanceof ProductVariant, 404);

        return $variant;
    }

    private function ensureInStore(Product $product): void
    {
        $demoSlug = StorefrontContext::slug();
        abort_unless($demoSlug === null || str_starts_with($product->slug, $demoSlug.'-'), 404);
    }

    private function ensureSellable(Product $product): void
    {
        abort_unless($product->is_active, 404);
        $this->ensureInStore($product);
    }
}
