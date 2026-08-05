<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_area_but_not_owner_portal(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('portal.dashboard'))->assertForbidden();
    }

    public function test_owner_can_access_portal_but_not_admin_area(): void
    {
        $owner = User::factory()->owner()->create();
        Tenant::factory()->create(['owner_user_id' => $owner->id]);

        $this->actingAs($owner)->get(route('portal.dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_owner_without_a_tenant_cannot_access_the_portal(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get(route('portal.dashboard'))->assertForbidden();
    }

    public function test_owner_cannot_view_another_owners_tenant(): void
    {
        $owner = User::factory()->owner()->create();
        Tenant::factory()->create(['owner_user_id' => $owner->id]);

        $otherOwner = User::factory()->owner()->create();
        $otherTenant = Tenant::factory()->create(['owner_user_id' => $otherOwner->id]);

        $this->actingAs($owner)
            ->get(route('portal.tenants.show', $otherTenant))
            ->assertForbidden();
    }

    public function test_owner_can_submit_content_request_only_within_plan_quota(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = Tenant::factory()->create(['owner_user_id' => $owner->id]);
        $plan = Plan::factory()->create(['content_request_quota' => 1]);
        Subscription::factory()->for($tenant)->for($plan)->create();

        $route = route('portal.tenants.content-requests.store', $tenant);

        $this->actingAs($owner)
            ->post($route, ['description' => 'Perbarui judul utama pada halaman depan.'])
            ->assertRedirect();

        $this->assertDatabaseHas('content_change_requests', [
            'tenant_id' => $tenant->id,
            'requested_by_user_id' => $owner->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('plan_feature_usages', [
            'tenant_id' => $tenant->id,
            'content_requests_used' => 1,
        ]);

        $this->actingAs($owner)
            ->post($route, ['description' => 'Ajukan perubahan konten untuk kedua kali.'])
            ->assertSessionHasErrors('description');

        $this->assertDatabaseCount('content_change_requests', 1);
    }
}
