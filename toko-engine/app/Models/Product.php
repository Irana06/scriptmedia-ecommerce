<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['category_id', 'name', 'slug', 'description', 'price', 'stock', 'is_featured', 'is_active'])]
class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product-images')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->nonQueued()->width(600)->height(450);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<ProductVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position')->orderBy('id');
    }

    /** @return Collection<int, ProductVariant> */
    public function activeVariants(): Collection
    {
        return $this->variants->where('is_active', true)->values();
    }

    public function hasVariants(): bool
    {
        return $this->activeVariants()->isNotEmpty();
    }

    /** Stock to show for the product as a whole. */
    public function availableStock(): int
    {
        return $this->hasVariants()
            ? (int) $this->activeVariants()->sum('stock')
            : $this->stock;
    }

    /** The price a shopper sees on the catalogue; the cheapest variant when there are variants. */
    public function displayPrice(): float
    {
        if (! $this->hasVariants()) {
            return (float) $this->price;
        }

        return (float) $this->activeVariants()->min('price');
    }

    public function hasPriceRange(): bool
    {
        if (! $this->hasVariants()) {
            return false;
        }

        $prices = $this->activeVariants()->pluck('price');

        return (float) $prices->min() !== (float) $prices->max();
    }

    public function maxPrice(): float
    {
        return $this->hasVariants()
            ? (float) $this->activeVariants()->max('price')
            : (float) $this->price;
    }

    /**
     * Option groups derived from the variants, in the order they first appear.
     *
     * @return array<string, list<string>>
     */
    public function optionGroups(): array
    {
        $groups = [];

        foreach ($this->activeVariants() as $variant) {
            foreach ($variant->optionPairs() as $group => $value) {
                $groups[$group] ??= [];

                if (! in_array($value, $groups[$group], true)) {
                    $groups[$group][] = $value;
                }
            }
        }

        return $groups;
    }

    /** @param Builder<Product> $query */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
