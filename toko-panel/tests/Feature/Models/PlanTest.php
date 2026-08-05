<?php

namespace Tests\Feature\Models;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_has_many_subscriptions(): void
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->for($plan)->for($tenant)->create();

        $this->assertTrue($plan->subscriptions->contains($subscription));
        $this->assertSame(50, $plan->max_products);
        $this->assertTrue($plan->is_active);
    }
}
