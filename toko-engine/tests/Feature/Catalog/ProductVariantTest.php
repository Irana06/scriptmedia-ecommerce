<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\OrderStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_variant_product_shows_its_option_groups_and_price_range(): void
    {
        $product = $this->variantProduct();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Ukuran')
            ->assertSee('Warna')
            ->assertSee('Rp100.000')
            ->assertSee('Rp150.000');

        $this->get(route('products.index'))
            ->assertOk()
            // The catalogue cannot add a variant product straight to the cart.
            ->assertSee('Pilih varian');
    }

    public function test_the_cart_keeps_two_variants_of_one_product_apart(): void
    {
        $product = $this->variantProduct();
        [$small, $large] = [$product->variants[0], $product->variants[1]];

        $this->post(route('cart.store', $product), ['quantity' => 2, 'variant_id' => $small->id]);
        $this->post(route('cart.store', $product), ['quantity' => 1, 'variant_id' => $large->id]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($small->name)
            ->assertSee($large->name);

        $this->assertSame(3, app(CartService::class)->count());
    }

    public function test_a_variant_product_cannot_be_added_without_choosing_one(): void
    {
        $product = $this->variantProduct();

        $this->post(route('cart.store', $product), ['quantity' => 1])
            ->assertSessionHasErrors('variant');

        $this->assertSame(0, app(CartService::class)->count());
    }

    public function test_an_unknown_variant_is_refused(): void
    {
        $product = $this->variantProduct();

        $this->post(route('cart.store', $product), ['quantity' => 1, 'variant_id' => 99999])
            ->assertNotFound();
    }

    public function test_ordering_takes_stock_from_the_variant_and_cancelling_gives_it_back(): void
    {
        $this->fakeMidtrans();
        $product = $this->variantProduct();
        $small = $product->variants[0];

        $this->post(route('cart.store', $product), ['quantity' => 2, 'variant_id' => $small->id]);
        $this->post(route('checkout.store'), [
            'customer_name' => 'Ayu Pelanggan',
            'customer_email' => 'ayu@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh No. 1',
            'payment_gateway_code' => MidtransService::GATEWAY_CODE,
        ])->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame($small->id, $item->product_variant_id);
        $this->assertSame($small->name, $item->variant_name);
        $this->assertSame(8, $small->fresh()->stock);
        // The product's own stock column is untouched when variants hold the stock.
        $this->assertSame(0, $product->fresh()->stock);

        app(OrderStockService::class)->cancel($order);

        $this->assertSame(10, $small->fresh()->stock);
    }

    public function test_an_owner_can_create_and_retire_variants_from_the_admin(): void
    {
        $category = Category::query()->create(['name' => 'Fashion', 'slug' => 'fashion', 'is_active' => true]);

        $this->actingAs($this->owner())->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Kaos Polos',
            'price' => 90000,
            'stock' => 0,
            'option_names' => ['Ukuran'],
            'variants' => [
                ['values' => ['S'], 'price' => 90000, 'stock' => 5, 'is_active' => '1'],
                ['values' => ['M'], 'price' => 95000, 'stock' => 3, 'is_active' => '1'],
            ],
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Kaos Polos')->firstOrFail();
        $this->assertSame(['S', 'M'], $product->variants->pluck('name')->all());
        $this->assertSame(['Ukuran' => ['S', 'M']], $product->optionGroups());
        $this->assertSame(8, $product->availableStock());

        // Submitting without the M row drops it, because nothing has ordered it.
        $this->actingAs($this->owner())->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'name' => 'Kaos Polos',
            'price' => 90000,
            'stock' => 0,
            'option_names' => ['Ukuran'],
            'variants' => [
                ['values' => ['S'], 'price' => 92000, 'stock' => 4, 'is_active' => '1'],
            ],
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh()->load('variants');
        $this->assertSame(['S'], $product->variants->pluck('name')->all());
        $this->assertSame('92000.00', $product->variants[0]->price);
    }

    public function test_a_variant_with_order_history_is_retired_rather_than_deleted(): void
    {
        $this->fakeMidtrans();
        $product = $this->variantProduct();
        $small = $product->variants[0];

        $this->post(route('cart.store', $product), ['quantity' => 1, 'variant_id' => $small->id]);
        $this->post(route('checkout.store'), [
            'customer_name' => 'Ayu',
            'customer_email' => 'ayu@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh',
            'payment_gateway_code' => MidtransService::GATEWAY_CODE,
        ]);

        $this->actingAs($this->owner())->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id,
            'name' => $product->name,
            'price' => 100000,
            'stock' => 0,
            'option_names' => ['Ukuran', 'Warna'],
            'variants' => [
                ['values' => ['L', 'Hitam'], 'price' => 150000, 'stock' => 4, 'is_active' => '1'],
            ],
        ])->assertRedirect();

        $small->refresh();
        $this->assertFalse($small->is_active);
        $this->assertSame($small->name, Order::query()->latest('id')->firstOrFail()->items()->firstOrFail()->variant_name);
    }

    private function variantProduct(): Product
    {
        $category = Category::query()->create(['name' => 'Fashion', 'slug' => 'fashion', 'is_active' => true]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Kaos Rimba',
            'slug' => 'kaos-rimba',
            'price' => 100000,
            'stock' => 0,
            'is_active' => true,
            'is_featured' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => 'S / Hitam',
            'options' => ProductVariant::encodeOptions(['Ukuran' => 'S', 'Warna' => 'Hitam']),
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
            'position' => 0,
        ]);
        ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => 'L / Hitam',
            'options' => ProductVariant::encodeOptions(['Ukuran' => 'L', 'Warna' => 'Hitam']),
            'price' => 150000,
            'stock' => 4,
            'is_active' => true,
            'position' => 1,
        ]);

        return $product->load('variants');
    }

    private function fakeMidtrans(): void
    {
        config()->set('database.connections.central.database', null);
        config()->set('services.midtrans.client_key', 'client-test');
        config()->set('services.midtrans.server_key', 'server-test');
        config()->set('services.midtrans.snap_url', 'https://app.sandbox.midtrans.com/snap/v1/transactions');
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'variant-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/variant-snap-token',
            ], 201),
        ]);
        PaymentGateway::query()->create([
            'code' => MidtransService::GATEWAY_CODE,
            'name' => 'Midtrans',
            'is_active' => true,
        ]);
    }

    private function owner(): User
    {
        foreach (['access admin', 'manage products'] as $permission) {
            Permission::findOrCreate($permission);
        }
        $role = Role::findOrCreate('owner');
        $role->syncPermissions(['access admin', 'manage products']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
