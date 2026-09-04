<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Database\Seeders\DemoStoreSeeder;
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
        $this->seed(DemoStoreSeeder::class);

        $this->get('/starter')->assertOk()->assertSee('Kedai Rona')->assertSee('Paket Starter')->assertSee('Kopi enak, tanpa ribet');
        $this->get('/standard')
            ->assertOk()
            ->assertSee('Shicomp Store')
            ->assertSee('Paket Standard')
            ->assertSee('Pencarian katalog')
            ->assertSee('/images/demo/shicomp-standard-hero.png');
        $this->get('/pro')
            ->assertOk()
            ->assertSee('Nara Atelier')
            ->assertSee('Paket Pro')
            ->assertSee('Curated collection')
            ->assertSee('/images/demo/nara-pro-hero.png');

        $this->get('/standard/products')
            ->assertOk()
            ->assertSee('Mechanical Keyboard K87')
            ->assertDontSee('Kopi Susu Rona');
        $this->get('/standard/products/standard-mechanical-keyboard-k87')
            ->assertOk()
            ->assertSee('Tambah ke keranjang');
        $this->post('/standard/cart/standard-mechanical-keyboard-k87', ['quantity' => 1])
            ->assertRedirect();
        $this->get('/standard/cart')
            ->assertOk()
            ->assertSee('Mechanical Keyboard K87')
            ->assertSee('/standard/checkout');

        $this->get('/starter/products')
            ->assertOk()
            ->assertDontSee('catalog-search');
        $this->get('/starter/products/starter-kopi-susu-rona')
            ->assertOk()
            ->assertDontSee('Kopi Gula Aren');

        $this->get('/standard/products?q=mouse')
            ->assertOk()
            ->assertSee('Wireless Mouse M2')
            ->assertDontSee('Mechanical Keyboard K87');

        $this->get('/pro/products?sort=price-high')
            ->assertOk()
            ->assertSeeInOrder(['Lumi Lounge Chair', 'Nami Woven Rug', 'Sora Pendant Lamp']);
        $this->get('/pro/products/pro-lumi-lounge-chair')
            ->assertOk()
            ->assertSee('Bagikan koleksi')
            ->assertSee('Arka Side Table');

        $this->get('/enterprise')->assertNotFound();
    }
}
