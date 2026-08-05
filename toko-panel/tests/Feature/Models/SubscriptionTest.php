<?php

namespace Tests\Feature\Models;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_belongs_to_tenant_current_and_pending_plans(): void
    {
        $tenant = Tenant::factory()->create();
        $currentPlan = Plan::factory()->create();
        $pendingPlan = Plan::factory()->standard()->create();
        $subscription = Subscription::factory()
            ->for($tenant)
            ->for($currentPlan)
            ->create(['pending_plan_id' => $pendingPlan->id]);

        $this->assertTrue($subscription->tenant->is($tenant));
        $this->assertTrue($subscription->plan->is($currentPlan));
        $this->assertTrue($subscription->pendingPlan->is($pendingPlan));
        $this->assertTrue($pendingPlan->pendingSubscriptions->contains($subscription));
    }
}
