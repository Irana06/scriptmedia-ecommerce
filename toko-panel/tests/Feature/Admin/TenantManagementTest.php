<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Tenants\ManageTenants;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_open_tenant_management(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();

        $this->actingAs($admin)->get(route('admin.tenants.index'))->assertOk();
        $this->actingAs($owner)->get(route('admin.tenants.index'))->assertForbidden();
    }

    public function test_admin_can_create_manual_tenant_with_pending_provisioning(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();
        $plan = Plan::factory()->create();

        Livewire::actingAs($admin)
            ->test(ManageTenants::class)
            ->set('name', 'Toko Baru')
            ->set('subdomain', 'toko-baru')
            ->set('ownerUserId', (string) $owner->id)
            ->set('planId', (string) $plan->id)
            ->set('billingCycle', 'annual')
            ->call('createTenant')
            ->assertHasNoErrors();

        $tenant = Tenant::query()->where('subdomain', 'toko-baru')->firstOrFail();

        $this->assertSame('pending', $tenant->provisioning_status);
        $this->assertSame('active', $tenant->store_status);
        $this->assertSame('tenant_toko_baru', $tenant->database_name);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'annual',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_suspend_and_reactivate_tenant(): void
    {
        $admin = User::factory()->admin()->create();
        $tenant = Tenant::factory()->create(['store_status' => 'active']);
        $component = Livewire::actingAs($admin)->test(ManageTenants::class);

        $component->call('toggleStoreStatus', $tenant->id);
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'store_status' => 'suspended']);

        $component->call('toggleStoreStatus', $tenant->id);
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'store_status' => 'active']);
    }
}
