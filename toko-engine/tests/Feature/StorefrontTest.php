<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_lists_featured_products_and_filters_catalog_by_category(): void
    {
        StoreSetting::query()->create(['store_name' => 'Toko Senja']);
        $home = Category::query()->create(['name' => 'Rumah', 'slug' => 'rumah', 'is_active' => true]);
        $fashion = Category::query()->create(['name' => 'Fashion', 'slug' => 'fashion', 'is_active' => true]);
        Product::query()->create([
            'category_id' => $home->id,
            'name' => 'Teko Tanah Sore',
            'slug' => 'teko-tanah-sore',
            'price' => 189000,
            'stock' => 10,
            'is_featured' => true,
            'is_active' => true,
        ]);
        Product::query()->create([
            'category_id' => $fashion->id,
            'name' => 'Tas Kanvas',
            'slug' => 'tas-kanvas',
            'price' => 245000,
            'stock' => 5,
            'is_featured' => false,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Toko Senja')
            ->assertSee('Teko Tanah Sore')
            ->assertDontSee('Tas Kanvas');

        $this->get(route('products.index', ['category' => 'fashion']))
            ->assertOk()
            ->assertSee('Tas Kanvas')
            ->assertDontSee('Teko Tanah Sore');
    }

    public function test_each_plan_has_a_public_demo_store(): void
    {
        $this->get('/starter')->assertOk()->assertSee('Kedai Rona')->assertSee('Paket Starter');
        $this->get('/standard')->assertOk()->assertSee('Shicomp Store')->assertSee('Paket Standard');
        $this->get('/pro')->assertOk()->assertSee('Nara Atelier')->assertSee('Paket Pro');
        $this->get('/enterprise')->assertNotFound();
    }
}
