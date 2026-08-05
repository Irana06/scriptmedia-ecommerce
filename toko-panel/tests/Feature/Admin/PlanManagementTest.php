<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Plans\ManagePlans;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_open_plan_management(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();

        $this->actingAs($admin)->get(route('admin.plans.index'))->assertOk();
        $this->actingAs($owner)->get(route('admin.plans.index'))->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_a_plan(): void
    {
        $admin = User::factory()->admin()->create();

        $component = Livewire::actingAs($admin)
            ->test(ManagePlans::class)
            ->call('createPlan')
            ->set('name', 'pro')
            ->set('slug', 'pro')
            ->set('pricePlatform', '3000000')
            ->set('priceCareMonthly', '750000')
            ->set('priceCareAnnual', '7500000')
            ->set('maxProducts', '')
            ->set('maxPaymentGateways', '')
            ->set('contentRequestQuota', '10')
            ->set('supportSlaHours', '4')
            ->set('customDomainAllowed', true)
            ->set('allowRealtimeShipping', true)
            ->set('allowFullDesignCustomization', true)
            ->set('sortOrder', '3')
            ->call('savePlan')
            ->assertHasNoErrors();

        $plan = Plan::query()->where('slug', 'pro')->firstOrFail();

        $this->assertNull($plan->max_products);
        $this->assertNull($plan->max_payment_gateways);

        $component
            ->call('editPlan', $plan->id)
            ->set('priceCareMonthly', '800000')
            ->call('savePlan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'price_care_monthly' => 800000,
        ]);

        $component->call('deletePlan', $plan->id)->assertHasNoErrors();
        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    public function test_plan_validation_rejects_duplicate_slug_and_negative_price(): void
    {
        $admin = User::factory()->admin()->create();
        Plan::factory()->create();

        Livewire::actingAs($admin)
            ->test(ManagePlans::class)
            ->call('createPlan')
            ->set('name', 'standard')
            ->set('slug', 'starter')
            ->set('pricePlatform', '-1')
            ->call('savePlan')
            ->assertHasErrors(['slug' => 'unique', 'pricePlatform' => 'min']);
    }

    public function test_plan_used_by_subscription_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $subscription = Subscription::factory()->create();

        Livewire::actingAs($admin)
            ->test(ManagePlans::class)
            ->call('deletePlan', $subscription->plan_id)
            ->assertHasErrors('deletePlan');

        $this->assertDatabaseHas('plans', ['id' => $subscription->plan_id]);
    }
}
