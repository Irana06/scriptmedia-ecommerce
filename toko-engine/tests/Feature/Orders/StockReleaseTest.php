<?php

namespace Tests\Feature\Orders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\MidtransService;
use App\Services\OrderStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_order_left_unpaid_past_the_window_is_cancelled_and_its_stock_returns(): void
    {
        $product = $this->product(stock: 5);
        $order = $this->orderFor($product, quantity: 2, placedAt: now()->subHours(30));

        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('failed', $order->payment_status);
        $this->assertNotNull($order->stock_restored_at);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_an_order_still_inside_the_window_is_left_alone(): void
    {
        $product = $this->product(stock: 5);
        $order = $this->orderFor($product, quantity: 2, placedAt: now()->subHours(2));

        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_a_paid_order_never_gives_its_stock_back(): void
    {
        $product = $this->product(stock: 5);
        $order = $this->orderFor($product, quantity: 2, placedAt: now()->subHours(30));
        $order->forceFill(['payment_status' => 'paid', 'paid_at' => now()])->save();

        $this->artisan('orders:expire-unpaid')->assertSuccessful();
        $this->assertFalse(app(OrderStockService::class)->release($order->fresh()));

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_releasing_twice_does_not_double_the_stock(): void
    {
        $product = $this->product(stock: 5);
        $order = $this->orderFor($product, quantity: 2, placedAt: now()->subHours(30));
        $stock = app(OrderStockService::class);

        $this->assertTrue($stock->release($order));
        $this->assertFalse($stock->release($order->fresh()));

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_cancelling_from_the_admin_returns_the_stock(): void
    {
        $product = $this->product(stock: 5);
        $order = $this->orderFor($product, quantity: 3, placedAt: now());

        $this->actingAs($this->adminUser())
            ->patch(route('admin.orders.update', $order), ['status' => 'cancelled', 'payment_status' => 'failed'])
            ->assertSessionHasNoErrors();

        $this->assertSame(8, $product->fresh()->stock);
        $this->assertNotNull($order->fresh()->stock_restored_at);
    }

    private function product(int $stock): Product
    {
        $category = Category::query()->create(['name' => 'Rumah', 'slug' => 'rumah', 'is_active' => true]);

        return Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Lampu Senja',
            'slug' => 'lampu-senja',
            'price' => 100000,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }

    private function orderFor(Product $product, int $quantity, mixed $placedAt): Order
    {
        $order = Order::query()->create([
            'number' => 'ORD-STOCK-'.$product->id.'-'.$quantity,
            'customer_name' => 'Ayu',
            'customer_email' => 'ayu@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh',
            'subtotal' => 100000 * $quantity,
            'total' => 100000 * $quantity,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_gateway_code' => MidtransService::GATEWAY_CODE,
            'placed_at' => $placedAt,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => 100000 * $quantity,
        ]);

        return $order;
    }

    private function adminUser(): User
    {
        foreach (['access admin', 'manage orders'] as $permission) {
            Permission::findOrCreate($permission);
        }
        $role = Role::findOrCreate('staff');
        $role->syncPermissions(['access admin', 'manage orders']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
