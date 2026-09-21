<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\StoreLimitService;
use Database\Seeders\DemoStoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DemoStoreAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string, 1: int|null}> */
    public static function planLimitProvider(): array
    {
        return [
            'starter caps at 50 products' => ['starter', 50],
            'standard caps at 150 products' => ['standard', 150],
            'pro is unlimited' => ['pro', null],
        ];
    }

    /** @return array<string, array{0: string}> */
    public static function planProvider(): array
    {
        return [
            'starter' => ['starter'],
            'standard' => ['standard'],
            'pro' => ['pro'],
        ];
    }

    #[DataProvider('planProvider')]
    public function test_a_store_owner_only_sees_their_own_catalogue(string $plan): void
    {
        $this->seed(DemoStoreSeeder::class);
        $owner = $this->storeOwner($plan);

        $mine = Product::query()->where('slug', 'like', $plan.'-%')->firstOrFail();
        $theirs = Product::query()->where('slug', 'not like', $plan.'-%')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee($mine->name)
            ->assertDontSee($theirs->name);
    }

    #[DataProvider('planLimitProvider')]
    public function test_the_plan_limit_follows_the_account(string $plan, ?int $expected): void
    {
        $this->seed(DemoStoreSeeder::class);
        config()->set('database.connections.central.database', null);

        $this->actingAs($this->storeOwner($plan));

        $this->assertSame($expected, app(StoreLimitService::class)->productLimit());
        $this->assertSame($plan, app(StoreLimitService::class)->planSlug());
    }

    public function test_another_stores_product_cannot_be_opened_or_edited(): void
    {
        $this->seed(DemoStoreSeeder::class);
        $owner = $this->storeOwner('starter');
        $theirs = Product::query()->where('slug', 'like', 'pro-%')->firstOrFail();

        $this->actingAs($owner)->get(route('admin.products.edit', $theirs))->assertNotFound();
        $this->actingAs($owner)->delete(route('admin.products.destroy', $theirs))->assertNotFound();
        $this->assertModelExists($theirs);
    }

    public function test_a_new_product_is_filed_under_the_owners_store(): void
    {
        $this->seed(DemoStoreSeeder::class);
        $owner = $this->storeOwner('starter');
        $category = Product::query()->where('slug', 'like', 'starter-%')->firstOrFail()->category;

        $this->actingAs($owner)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Es Kopi Baru',
            'price' => 25000,
            'stock' => 10,
            'is_active' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Es Kopi Baru')->firstOrFail();
        $this->assertSame('starter-es-kopi-baru', $product->slug);

        // It shows up on the Starter storefront it was created for.
        $this->get('/starter/products')->assertOk()->assertSee('Es Kopi Baru');
        $this->get('/pro/products')->assertOk()->assertDontSee('Es Kopi Baru');
    }

    public function test_orders_are_scoped_to_the_store_they_were_placed_at(): void
    {
        $this->seed(DemoStoreSeeder::class);
        $mine = $this->order('starter', 'ORD-STARTER-1', 'Pembeli Starter');
        $theirs = $this->order('pro', 'ORD-PRO-1', 'Pembeli Pro');

        $this->actingAs($this->storeOwner('starter'))
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($mine->number)
            ->assertDontSee($theirs->number);

        $this->actingAs($this->storeOwner('starter'))
            ->get(route('admin.orders.show', $theirs))
            ->assertNotFound();
    }

    public function test_an_unbound_account_still_administers_everything(): void
    {
        $this->seed(DemoStoreSeeder::class);
        $owner = $this->storeOwner(null);

        $starter = Product::query()->where('slug', 'like', 'starter-%')->firstOrFail();
        $pro = Product::query()->where('slug', 'like', 'pro-%')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('admin.products.index', ['search' => $starter->name]))
            ->assertOk()
            ->assertSee($starter->name);

        $this->actingAs($owner)->get(route('admin.products.edit', $pro))->assertOk();
    }

    private function storeOwner(?string $plan): User
    {
        $permissions = ['access admin', 'manage products', 'manage orders', 'view reports'];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        $role = Role::findOrCreate('owner');
        $role->syncPermissions($permissions);

        $user = User::factory()->create(['demo_store' => $plan]);
        $user->assignRole($role);

        return $user;
    }

    private function order(string $plan, string $number, string $customer): Order
    {
        return Order::query()->create([
            'number' => $number,
            'demo_store' => $plan,
            'customer_name' => $customer,
            'customer_email' => 'pembeli@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh',
            'subtotal' => 50000,
            'total' => 50000,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_gateway_code' => 'midtrans',
            'placed_at' => now(),
        ]);
    }
}
