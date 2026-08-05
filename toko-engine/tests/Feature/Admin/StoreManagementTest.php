<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_manage_products_store_settings_and_reports(): void
    {
        foreach (['access admin', 'manage products', 'manage store settings', 'view reports'] as $permissionName) {
            Permission::findOrCreate($permissionName);
        }
        $ownerRole = Role::findOrCreate('owner');
        $ownerRole->syncPermissions(['access admin', 'manage products', 'manage store settings', 'view reports']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $category = Category::query()->create(['name' => 'Rumah', 'slug' => 'rumah', 'is_active' => true]);
        StoreSetting::query()->create(['store_name' => 'Toko Lama']);

        $this->actingAs($owner)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Cangkir Pagi',
                'description' => 'Cangkir keramik lokal.',
                'price' => 99000,
                'stock' => 7,
                'is_active' => '1',
                'is_featured' => '1',
            ])
            ->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['slug' => 'cangkir-pagi', 'stock' => 7, 'is_active' => true]);

        $this->actingAs($owner)
            ->put(route('admin.store-settings.update'), [
                'store_name' => 'Toko Baru',
                'tagline' => 'Produk pilihan.',
                'contact_email' => 'halo@example.com',
            ])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('store_settings', ['store_name' => 'Toko Baru']);

        Order::query()->create([
            'number' => 'ORD-TEST-001',
            'customer_name' => 'Pelanggan',
            'customer_email' => 'pelanggan@example.com',
            'customer_phone' => '08123',
            'shipping_address' => 'Alamat',
            'subtotal' => 99000,
            'total' => 99000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_gateway_code' => 'manual-transfer',
            'placed_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Rp99.000')
            ->assertSee('ORD-TEST-001');
    }
}
