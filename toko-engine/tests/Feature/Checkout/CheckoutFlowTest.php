<?php

namespace Tests\Feature\Checkout;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout_and_staff_can_process_the_order(): void
    {
        StoreSetting::query()->create(['store_name' => 'Toko Senja']);
        $category = Category::query()->create(['name' => 'Rumah', 'slug' => 'rumah', 'is_active' => true]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Teko Tanah Sore',
            'slug' => 'teko-tanah-sore',
            'description' => 'Teko pilihan.',
            'price' => 189000,
            'stock' => 10,
            'is_featured' => true,
            'is_active' => true,
        ]);
        $gateway = PaymentGateway::query()->create([
            'code' => 'manual-transfer',
            'name' => 'Transfer Manual',
            'instructions' => 'Transfer ke rekening demo.',
            'is_active' => true,
        ]);

        $this->post(route('cart.store', $product), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->get(route('checkout.create'))->assertOk()->assertSee('Transfer Manual');
        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Ayu Pelanggan',
            'customer_email' => 'ayu@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh No. 1',
            'payment_gateway_code' => $gateway->code,
        ]);

        $order = Order::query()->firstOrFail();
        $response->assertRedirect();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'line_total' => 378000,
        ]);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertEmpty(session('storefront_cart'));
        $this->assertNotNull($order->public_token);
        $this->get(route('orders.track', $order->public_token))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee('Simpan lewat WhatsApp');
        $this->get(route('orders.track', str_repeat('x', 64)))->assertNotFound();

        foreach (['access admin', 'manage orders'] as $permissionName) {
            Permission::findOrCreate($permissionName);
        }
        $staffRole = Role::findOrCreate('staff');
        $staffRole->syncPermissions(['access admin', 'manage orders']);
        $staff = User::factory()->create();
        $staff->assignRole($staffRole);

        $this->actingAs($staff)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Ayu Pelanggan')
            ->assertSee('Teko Tanah Sore');

        $this->actingAs($staff)
            ->patch(route('admin.orders.update', $order), ['status' => 'processing', 'payment_status' => 'paid'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing', 'payment_status' => 'paid']);
    }
}
