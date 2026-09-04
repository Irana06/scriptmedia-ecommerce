<?php

namespace App\Services;

use App\Models\Product;
use App\Support\StorefrontContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class WishlistService
{
    private const SESSION_KEY = 'storefront_wishlist_pro';

    /** @return list<int> */
    public function productIds(): array
    {
        $ids = Session::get(self::SESSION_KEY, []);

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($ids, 'is_numeric'))));
    }

    public function contains(Product $product): bool
    {
        return in_array($product->id, $this->productIds(), true);
    }

    public function add(Product $product): void
    {
        Session::put(self::SESSION_KEY, [...$this->productIds(), $product->id]);
    }

    public function remove(Product $product): void
    {
        Session::put(self::SESSION_KEY, array_values(array_diff($this->productIds(), [$product->id])));
    }

    public function count(): int
    {
        return count($this->productIds());
    }

    /** @return Collection<int, Product> */
    public function products(): Collection
    {
        return StorefrontContext::scopeProducts(Product::query())
            ->available()
            ->with(['category', 'media'])
            ->whereIn('id', $this->productIds())
            ->get();
    }
}
