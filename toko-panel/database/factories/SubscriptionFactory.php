<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodStart = today()->startOfMonth();

        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'current_period_start' => $periodStart,
            'current_period_end' => $periodStart->copy()->endOfMonth(),
            'next_billing_date' => $periodStart->copy()->addMonth(),
            'pending_plan_id' => null,
        ];
    }
}
