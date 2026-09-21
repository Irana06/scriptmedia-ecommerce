<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\CartLine;
use App\Support\StorefrontContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * The cart is keyed by product *and* variant, because the same product in two
 * sizes is two different things to buy.
 *
 * @phpstan-type StoredLine array{key: string, product_id: int, variant_id: int|null, quantity: int}
 */
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

    public static function lineKey(Product $product, ?ProductVariant $variant = null): string
    {
        return $variant === null ? 'p'.$product->id : 'p'.$product->id.'-v'.$variant->id;
    }

    /**
     * @return array<string, StoredLine>
     */
    public function raw(): array
    {
        $cart = Session::get($this->sessionKey(), []);

        if (! is_array($cart)) {
            return [];
        }

        $normalised = [];

        foreach ($cart as $key => $line) {
            // Carts saved before variants existed were a plain [productId => quantity] map.
            if (is_numeric($key) && is_numeric($line)) {
                $quantity = (int) $line;

                if ($quantity > 0) {
                    $normalised['p'.(int) $key] = [
                        'key' => 'p'.(int) $key,
                        'product_id' => (int) $key,
                        'variant_id' => null,
                        'quantity' => $quantity,
                    ];
                }

                continue;
            }

            if (! is_string($key) || ! is_array($line) || ! is_numeric($line['product_id'] ?? null)) {
                continue;
            }

            $quantity = is_numeric($line['quantity'] ?? null) ? (int) $line['quantity'] : 0;

            if ($quantity < 1) {
                continue;
            }

            $variantId = is_numeric($line['variant_id'] ?? null) ? (int) $line['variant_id'] : null;

            $normalised[$key] = [
                'key' => $key,
                'product_id' => (int) $line['product_id'],
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ];
        }

        return $normalised;
    }

    public function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): void
    {
        $key = self::lineKey($product, $variant);
        $cart = $this->raw();
        $ceiling = $variant === null ? $product->stock : $variant->stock;

        $cart[$key] = [
            'key' => $key,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => min(($cart[$key]['quantity'] ?? 0) + $quantity, $ceiling),
        ];

        Session::put($this->sessionKey(), $cart);
        $this->select([...$this->selectedKeys(), $key]);
    }

    public function update(Product $product, int $quantity, ?ProductVariant $variant = null): void
    {
        if ($quantity <= 0) {
            $this->remove($product, $variant);

            return;
        }

        $key = self::lineKey($product, $variant);
        $cart = $this->raw();

        if (! array_key_exists($key, $cart)) {
            return;
        }

        $cart[$key]['quantity'] = min($quantity, $variant === null ? $product->stock : $variant->stock);
        Session::put($this->sessionKey(), $cart);
    }

    public function remove(Product $product, ?ProductVariant $variant = null): void
    {
        $this->forget([self::lineKey($product, $variant)]);
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey());
        Session::forget($this->selectionKey());
    }

    /**
     * Drop only the given lines, leaving the rest of the cart untouched.
     *
     * @param  iterable<string>  $keys
     */
    public function forget(iterable $keys): void
    {
        $cart = $this->raw();
        $selected = $this->selectedKeys();

        foreach ($keys as $key) {
            unset($cart[(string) $key]);
            $selected = array_diff($selected, [(string) $key]);
        }

        Session::put($this->sessionKey(), $cart);
        $this->select($selected);
    }

    public function count(): int
    {
        return array_sum(array_column($this->raw(), 'quantity'));
    }

    /**
     * Cart keys the shopper has ticked for checkout.
     *
     * @return list<string>
     */
    public function selectedKeys(): array
    {
        $selected = Session::get($this->selectionKey());
        $inCart = array_keys($this->raw());

        // Carts created before selection existed have every line ready to order.
        if (! is_array($selected)) {
            return $inCart;
        }

        return array_values(array_intersect(
            array_unique(array_map('strval', array_filter($selected, 'is_scalar'))),
            $inCart,
        ));
    }

    /** @param iterable<string> $keys */
    public function select(iterable $keys): void
    {
        $inCart = array_keys($this->raw());
        $selected = [];

        foreach ($keys as $key) {
            if (in_array((string) $key, $inCart, true)) {
                $selected[] = (string) $key;
            }
        }

        Session::put($this->selectionKey(), array_values(array_unique($selected)));
    }

    /** Restrict the selection to a single line, as buy-now does. */
    public function selectOnly(Product $product, ?ProductVariant $variant = null): void
    {
        $this->select([self::lineKey($product, $variant)]);
    }

    public function selectedCount(): int
    {
        return count($this->selectedKeys());
    }

    /**
     * Every line in the cart.
     *
     * @return Collection<int, CartLine>
     */
    public function items(): Collection
    {
        $cart = $this->raw();

        if ($cart === []) {
            return new Collection;
        }

        $selected = $this->selectedKeys();
        $products = StorefrontContext::scopeProducts(Product::query())
            ->available()
            ->with(['category', 'media', 'variants'])
            ->whereIn('id', array_column($cart, 'product_id'))
            ->get()
            ->keyBy('id');

        $items = [];

        foreach ($cart as $line) {
            $product = $products->get($line['product_id']);

            if (! $product instanceof Product) {
                continue;
            }

            $variant = null;

            if ($line['variant_id'] !== null) {
                $variant = $product->activeVariants()->firstWhere('id', $line['variant_id']);

                // The variant was removed or switched off while it sat in the cart.
                if (! $variant instanceof ProductVariant) {
                    continue;
                }
            }

            // A product that grew variants cannot be ordered without picking one.
            if ($variant === null && $product->hasVariants()) {
                continue;
            }

            $stock = $variant === null ? $product->stock : $variant->stock;
            $quantity = min($line['quantity'], $stock);

            if ($quantity < 1) {
                continue;
            }

            $items[] = new CartLine(
                key: $line['key'],
                product: $product,
                variant: $variant,
                unitPrice: (float) ($variant === null ? $product->price : $variant->price),
                quantity: $quantity,
                stock: $stock,
                selected: in_array($line['key'], $selected, true),
            );
        }

        return new Collection($items);
    }

    /**
     * The lines that checkout will turn into an order.
     *
     * @return Collection<int, CartLine>
     */
    public function selectedItems(): Collection
    {
        $selected = [];

        foreach ($this->items() as $item) {
            if ($item->selected) {
                $selected[] = $item;
            }
        }

        return new Collection($selected);
    }

    public function subtotal(): float
    {
        return $this->items()->sum(fn (CartLine $line): float => $line->lineTotal());
    }

    public function selectedSubtotal(): float
    {
        return $this->selectedItems()->sum(fn (CartLine $line): float => $line->lineTotal());
    }
}
