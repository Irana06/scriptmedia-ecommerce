<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
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
        ]);
    }

    public function store(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $validated = $request->validate(['quantity' => ['nullable', 'integer', 'min:1']]);

        if ($product->stock < 1) {
            return back()->withErrors(['quantity' => 'Produk sedang habis.']);
        }

        $cart->add($product, (int) ($validated['quantity'] ?? 1));

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.$product->stock],
        ]);
        $cart->update($product, (int) $validated['quantity']);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Product $product, CartService $cart): RedirectResponse
    {
        $cart->remove($product);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
