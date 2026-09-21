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

    private function selectionKey(): string
    {
        return $this->sessionKey().'_selected';
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
        $this->select([...$this->selectedIds(), $product->id]);
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
        $this->select(array_diff($this->selectedIds(), [$product->id]));
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey());
        Session::forget($this->selectionKey());
    }

    /**
     * Drop only the given products, leaving the rest of the cart untouched.
     *
     * @param  iterable<int>  $productIds
     */
    public function forget(iterable $productIds): void
    {
        $cart = $this->raw();
        $selected = $this->selectedIds();

        foreach ($productIds as $productId) {
            unset($cart[(int) $productId]);
            $selected = array_diff($selected, [(int) $productId]);
        }

        Session::put($this->sessionKey(), $cart);
        $this->select($selected);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /**
     * Product ids the shopper has ticked for checkout.
     *
     * @return list<int>
     */
    public function selectedIds(): array
    {
        $selected = Session::get($this->selectionKey());

        // Carts created before selection existed have every line ready to order.
        if (! is_array($selected)) {
            return array_keys($this->raw());
        }

        $inCart = array_keys($this->raw());

        return array_values(array_intersect(
            array_unique(array_map('intval', array_filter($selected, 'is_numeric'))),
            $inCart,
        ));
    }

    /** @param iterable<int> $productIds */
    public function select(iterable $productIds): void
    {
        $inCart = array_keys($this->raw());
        $ids = [];

        foreach ($productIds as $productId) {
            if (in_array((int) $productId, $inCart, true)) {
                $ids[] = (int) $productId;
            }
        }

        Session::put($this->selectionKey(), array_values(array_unique($ids)));
    }

    /** Restrict the selection to a single product, as buy-now does. */
    public function selectOnly(Product $product): void
    {
        $this->select([$product->id]);
    }

    public function isSelected(Product $product): bool
    {
        return in_array($product->id, $this->selectedIds(), true);
    }

    public function selectedCount(): int
    {
        return count($this->selectedIds());
    }

    /**
     * Every line in the cart.
     *
     * @return Collection<int, array{product: Product, quantity: int, line_total: float, selected: bool}>
     */
    public function items(): Collection
    {
        $cart = $this->raw();
        $selected = $this->selectedIds();
        $products = StorefrontContext::scopeProducts(Product::query())
            ->available()
            ->with(['category', 'media'])
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(function (int $quantity, int $productId) use ($products, $selected): ?array {
                $product = $products->get($productId);

                if (! $product instanceof Product) {
                    return null;
                }

                $quantity = min($quantity, $product->stock);

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => (float) $product->price * $quantity,
                    'selected' => in_array($productId, $selected, true),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * The lines that checkout will turn into an order.
     *
     * @return Collection<int, array{product: Product, quantity: int, line_total: float, selected: bool}>
     */
    public function selectedItems(): Collection
    {
        return $this->items()->where('selected', true)->values();
    }

    public function subtotal(): float
    {
        return $this->items()->sum('line_total');
    }

    public function selectedSubtotal(): float
    {
        return $this->selectedItems()->sum('line_total');
    }
}
