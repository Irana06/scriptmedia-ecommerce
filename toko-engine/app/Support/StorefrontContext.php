<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

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
