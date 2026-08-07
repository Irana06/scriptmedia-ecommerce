<?php

namespace Tests\Feature\EndToEnd;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        DB::purge('central');

        parent::tearDown();
    }

    public function test_customer_checkout_reaches_admin_and_plan_limit_blocks_the_next_product(): void
    {
        $this->configureCentralPlan(maxProducts: 1);
        StoreSetting::query()->create(['store_name' => 'Toko Alur']);
        $category = Category::query()->create(['name' => 'Rumah', 'slug' => 'rumah', 'is_active' => true]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Lampu Senja',
            'slug' => 'lampu-senja',
            'description' => 'Lampu meja buatan lokal.',
            'price' => 175000,
            'stock' => 5,
            'is_featured' => true,
            'is_active' => true,
        ]);
        $gateway = PaymentGateway::query()->create([
            'code' => 'manual-transfer',
            'name' => 'Transfer Manual',
            'instructions' => 'Transfer ke rekening toko.',
            'is_active' => true,
        ]);

        $this->get(route('home'))->assertOk()->assertSee('Lampu Senja');
        $this->get(route('products.index'))->assertOk()->assertSee('Lampu Senja');
        $this->get(route('products.show', $product))->assertOk()->assertSee('Tambah ke keranjang');
        $this->post(route('cart.store', $product), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->get(route('checkout.create'))->assertOk()->assertSee('Transfer Manual');

        $checkout = $this->post(route('checkout.store'), [
            'customer_name' => 'Ayu Pelanggan',
            'customer_email' => 'ayu@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Toko No. 12',
            'payment_gateway_code' => $gateway->code,
        ]);

        $order = Order::query()->firstOrFail();
        $checkout->assertRedirect();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $owner = $this->ownerWithPermissions();
        $this->actingAs($owner)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee('Ayu Pelanggan');
        $this->actingAs($owner)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee('Rp350.000');

        $limitResponse = $this->actingAs($owner)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Produk Melebihi Paket',
            'price' => 99000,
            'stock' => 3,
            'is_active' => '1',
        ]);

        $limitResponse->assertSessionHasErrors('limit');
        $this->assertStringContainsString('Batas paket tercapai (1 produk)', session('errors')->first('limit'));
        $this->assertDatabaseMissing('products', ['slug' => 'produk-melebihi-paket']);
    }

    public function test_standalone_store_remains_unlimited_when_central_connection_is_disabled(): void
    {
        config()->set('database.connections.central.database', null);
        config()->set('store-limits.fallback.max_products', null);
        $owner = $this->ownerWithPermissions();
        $category = Category::query()->create(['name' => 'Gaya Hidup', 'slug' => 'gaya-hidup', 'is_active' => true]);

        $this->actingAs($owner)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Produk Standalone',
                'description' => 'Tetap berjalan tanpa panel pusat.',
                'price' => 75000,
                'stock' => 10,
                'is_active' => '1',
                'is_featured' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['slug' => 'produk-standalone']);
        $this->get(route('products.index'))->assertOk()->assertSee('Produk Standalone');
    }

    private function configureCentralPlan(int $maxProducts): void
    {
        config()->set('database.connections.central', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('store-limits.tenant_database', 'tenant_e2e');
        DB::purge('central');

        Schema::connection('central')->create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_payment_gateways')->nullable();
        });
        Schema::connection('central')->create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('database_name');
            $table->string('store_status');
        });
        Schema::connection('central')->create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('plan_id');
            $table->string('status');
        });

        DB::connection('central')->table('plans')->insert([
            'id' => 1,
            'slug' => 'starter',
            'max_products' => $maxProducts,
            'max_payment_gateways' => 1,
        ]);
        DB::connection('central')->table('tenants')->insert([
            'id' => 1,
            'database_name' => 'tenant_e2e',
            'store_status' => 'active',
        ]);
        DB::connection('central')->table('subscriptions')->insert([
            'id' => 1,
            'tenant_id' => 1,
            'plan_id' => 1,
            'status' => 'active',
        ]);
    }

    private function ownerWithPermissions(): User
    {
        foreach (['access admin', 'manage products', 'manage orders', 'view reports'] as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $owner = User::factory()->create();
        $owner->givePermissionTo(['access admin', 'manage products', 'manage orders', 'view reports']);

        return $owner;
    }
}
