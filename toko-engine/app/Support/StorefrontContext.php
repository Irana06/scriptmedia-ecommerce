<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StorefrontContext
{
    public static function slug(): ?string
    {
        $slug = request()->attributes->get('demo_store_slug') ?? request()->route('demoStore');

        return is_string($slug) && array_key_exists($slug, config('demo-stores')) ? $slug : null;
    }

    /** @return array<string, mixed>|null */
    public static function store(): ?array
    {
        $slug = self::slug();

        return $slug === null ? null : config("demo-stores.{$slug}");
    }

    public static function allows(string $feature): bool
    {
        $store = self::store();

        return $store === null || ($store[$feature] ?? false) === true;
    }

    /**
     * The demo store an admin account is bound to.
     *
     * Separate from slug(), which follows the URL: an owner signed in to the
     * Starter demo should still see the whole root storefront when they visit it.
     */
    public static function adminSlug(): ?string
    {
        $slug = auth()->user()?->demo_store;

        return is_string($slug) && array_key_exists($slug, config('demo-stores')) ? $slug : null;
    }

    /** @return array<string, mixed>|null */
    public static function adminStore(): ?array
    {
        $slug = self::adminSlug();

        return $slug === null ? null : config("demo-stores.{$slug}");
    }

    /**
     * Restrict an admin query to the store its account is bound to.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function scopeAdminBySlug(Builder $query, string $column = 'slug'): Builder
    {
        $slug = self::adminSlug();

        return $slug === null ? $query : $query->where($column, 'like', $slug.'-%');
    }

    /**
     * Payment gateway codes this storefront offers.
     *
     * @return list<string>|null Null means every active gateway is offered.
     */
    public static function gatewayCodes(): ?array
    {
        $codes = self::store()['gateways'] ?? null;

        if (! is_array($codes)) {
            return null;
        }

        $codes = array_values(array_filter($codes, fn (mixed $code): bool => is_string($code) && $code !== ''));

        return $codes === [] ? null : $codes;
    }

    /**
     * Demo artwork (emoji plus background colour) configured for a product.
     *
     * @return array{icon: string, color: string}|null
     */
    public static function productVisual(Product $product): ?array
    {
        $store = self::store();
        $slug = self::slug();

        if ($store === null || $slug === null || ! is_array($store['products'] ?? null)) {
            return null;
        }

        foreach ($store['products'] as $configured) {
            if (! is_array($configured) || ! is_string($configured['name'] ?? null)) {
                continue;
            }

            if ($slug.'-'.Str::slug($configured['name']) !== $product->slug) {
                continue;
            }

            $icon = $configured['icon'] ?? null;
            $color = $configured['color'] ?? null;

            return is_string($icon) && is_string($color) ? ['icon' => $icon, 'color' => $color] : null;
        }

        return null;
    }

    public static function routeName(string $name): string
    {
        return self::slug() === null ? $name : 'demo.'.$name;
    }

    public static function route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $slug = self::slug();
        if ($slug === null) {
            return route($name, $parameters, $absolute);
        }

        $parameters = is_array($parameters) ? $parameters : [$parameters];

        return route('demo.'.$name, ['demoStore' => $slug, ...$parameters], $absolute);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public static function routeParameters(array $parameters = []): array
    {
        $slug = self::slug();

        return $slug === null ? $parameters : ['demoStore' => $slug, ...$parameters];
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public static function scopeProducts(Builder $query): Builder
    {
        $slug = self::slug();

        return $slug === null ? $query : $query->where('slug', 'like', $slug.'-%');
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public static function scopeCategories(Builder $query): Builder
    {
        $slug = self::slug();

        return $slug === null ? $query : $query->where('slug', 'like', $slug.'-%');
    }
}
