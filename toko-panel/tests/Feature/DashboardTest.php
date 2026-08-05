<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admins_are_redirected_to_the_admin_dashboard(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_dashboard_reports_tenant_statuses_and_mrr(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create(['price_care_monthly' => 500000]);
        $activeTenant = Tenant::factory()->create(['store_status' => 'active']);
        Tenant::factory()->create(['store_status' => 'suspended']);
        Subscription::factory()->for($activeTenant)->for($plan)->create([
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('metrics', function (array $metrics): bool {
                return $metrics['active_tenants'] === 1
                    && $metrics['tenant_statuses']['suspended'] === 1
                    && $metrics['mrr'] === 500000.0;
            });
    }
}
