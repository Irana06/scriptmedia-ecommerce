<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\User;
use App\Services\StoreLimitService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        DB::purge('central');

        parent::tearDown();
    }

    public function test_standalone_mode_is_unlimited_and_supports_local_fallback_limits(): void
    {
        config()->set('database.connections.central.database', null);
        config()->set('store-limits.fallback.max_products', null);

        $this->assertTrue(app(StoreLimitService::class)->canAddProduct());

        config()->set('store-limits.fallback.max_products', 0);

        $this->assertFalse(app(StoreLimitService::class)->canAddProduct());
    }

    public function test_failed_central_query_falls_back_without_breaking_the_store(): void
    {
        $this->configureCentralSqlite();
        config()->set('store-limits.fallback.max_products', null);

        $this->assertTrue(app(StoreLimitService::class)->canAddProduct());
    }

    public function test_central_plan_blocks_products_and_gateway_activation_at_the_limit(): void
    {
        $this->configureCentralPlan(maxProducts: 2, maxGateways: 1);
        $owner = $this->ownerWithPermissions();
        $category = Category::query()->create([
            'name' => 'Rumah',
            'slug' => 'rumah',
            'is_active' => true,
        ]);

        foreach (range(1, 2) as $number) {
            Product::query()->create([
                'category_id' => $category->id,
                'name' => "Produk {$number}",
                'slug' => "produk-{$number}",
                'price' => 10000,
                'stock' => 1,
                'is_active' => true,
            ]);
        }

        $this->actingAs($owner)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Produk Ketiga',
                'price' => 10000,
                'stock' => 1,
            ])
            ->assertSessionHasErrors('limit');

        $this->assertDatabaseMissing('products', ['slug' => 'produk-ketiga']);

        PaymentGateway::query()->create([
            'code' => 'manual',
            'name' => 'Manual',
            'is_active' => true,
        ]);
        $inactiveGateway = PaymentGateway::query()->create([
            'code' => 'xendit',
            'name' => 'Xendit',
            'is_active' => false,
        ]);

        $this->actingAs($owner)
            ->patch(route('admin.payment-gateways.update', $inactiveGateway), ['is_active' => true])
            ->assertSessionHasErrors('gateway_limit');

        $this->assertFalse($inactiveGateway->fresh()->is_active);
    }

    private function configureCentralPlan(int $maxProducts, int $maxGateways): void
    {
        $this->configureCentralSqlite();

        Schema::connection('central')->create('plans', function (Blueprint $table): void {
            $table->id();
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
            'max_products' => $maxProducts,
            'max_payment_gateways' => $maxGateways,
        ]);
        DB::connection('central')->table('tenants')->insert([
            'id' => 1,
            'database_name' => 'tenant_store',
            'store_status' => 'active',
        ]);
        DB::connection('central')->table('subscriptions')->insert([
            'id' => 1,
            'tenant_id' => 1,
            'plan_id' => 1,
            'status' => 'active',
        ]);
    }

    private function configureCentralSqlite(): void
    {
        config()->set('database.connections.central', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('store-limits.tenant_database', 'tenant_store');
        DB::purge('central');
    }

    private function ownerWithPermissions(): User
    {
        foreach (['access admin', 'manage products', 'manage store settings'] as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $owner = User::factory()->create();
        $owner->givePermissionTo(['access admin', 'manage products', 'manage store settings']);

        return $owner;
    }
}
