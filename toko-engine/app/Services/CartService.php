<?php

namespace App\Services;

use App\Models\Product;
use App\Support\StorefrontContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private function sessionKey(): string
    {
        return 'storefront_cart'.(StorefrontContext::slug() ? '_'.StorefrontContext::slug() : '');
    }

    /** @return array<int, int> */
    public function raw(): array
    {
        $cart = Session::get($this->sessionKey(), []);

        if (! is_array($cart)) {
            return [];
        }

        $normalized = [];
        foreach ($cart as $productId => $quantity) {
            if (is_numeric($productId) && is_numeric($quantity) && (int) $quantity > 0) {
                $normalized[(int) $productId] = (int) $quantity;
            }
        }

        return $normalized;
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->raw();
        $cart[$product->id] = min(($cart[$product->id] ?? 0) + $quantity, $product->stock);
        Session::put($this->sessionKey(), $cart);
    }

    public function update(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($product);

            return;
        }

        $cart = $this->raw();
        $cart[$product->id] = min($quantity, $product->stock);
        Session::put($this->sessionKey(), $cart);
    }

    public function remove(Product $product): void
    {
        $cart = $this->raw();
        unset($cart[$product->id]);
        Session::put($this->sessionKey(), $cart);
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey());
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /** @return Collection<int, array{product: Product, quantity: int, line_total: float}> */
    public function items(): Collection
    {
        $cart = $this->raw();
        $products = StorefrontContext::scopeProducts(Product::query())
            ->available()
            ->with(['category', 'media'])
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(function (int $quantity, int $productId) use ($products): ?array {
                $product = $products->get($productId);

                if (! $product instanceof Product) {
                    return null;
                }

                $quantity = min($quantity, $product->stock);

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => (float) $product->price * $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function subtotal(): float
    {
        return $this->items()->sum('line_total');
    }
}
