<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;

/**
 * One line in the cart: a product, optionally a variant of it, and how many.
 *
 * Kept as an object rather than an array so the shape is checked once here
 * instead of at every place that reads a line.
 */
final readonly class CartLine
{
    public function __construct(
        public string $key,
        public Product $product,
        public ?ProductVariant $variant,
        public float $unitPrice,
        public int $quantity,
        public int $stock,
        public bool $selected,
    ) {}

    /** Product name with its variant, as the shopper picked it. */
    public function label(): string
    {
        return $this->variant === null
            ? $this->product->name
            : $this->product->name.' — '.$this->variant->name;
    }

    public function lineTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }
}
