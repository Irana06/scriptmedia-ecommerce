<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['product_id', 'name', 'sku', 'options', 'price', 'stock', 'is_active', 'position'])]
class ProductVariant extends Model
{
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }

    /** @param Builder<ProductVariant> $query */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * The attribute pairs this variant stands for.
     *
     * @return array<string, string>
     */
    public function optionPairs(): array
    {
        $options = $this->getAttribute('options');

        if (! is_array($options)) {
            return [];
        }

        $pairs = [];

        foreach ($options as $group => $value) {
            /*
             * Options are stored as an ordered list of {group, value} pairs, because
             * MySQL sorts the keys of a JSON object and would scramble the order the
             * shop owner arranged. Plain objects are still read for older rows.
             */
            if (is_array($value)) {
                $group = $value['group'] ?? null;
                $value = $value['value'] ?? null;
            }

            if (is_string($group) && $group !== '' && is_string($value) && $value !== '') {
                $pairs[$group] = $value;
            }
        }

        return $pairs;
    }

    /**
     * @param  array<string, string>  $pairs
     * @return list<array{group: string, value: string}>
     */
    public static function encodeOptions(array $pairs): array
    {
        $encoded = [];

        foreach ($pairs as $group => $value) {
            $encoded[] = ['group' => $group, 'value' => $value];
        }

        return $encoded;
    }
}
