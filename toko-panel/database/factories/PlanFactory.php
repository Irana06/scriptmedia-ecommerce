<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'starter',
            'slug' => 'starter',
            'price_platform' => 1500000,
            'price_care_monthly' => 250000,
            'price_care_annual' => 2500000,
            'max_products' => 50,
            'max_payment_gateways' => 1,
            'content_request_quota' => 1,
            'support_sla_hours' => 24,
            'custom_domain_allowed' => false,
            'allow_realtime_shipping' => false,
            'allow_full_design_customization' => false,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }

    public function standard(): static
    {
        return $this->state(fn () => [
            'name' => 'standard',
            'slug' => 'standard',
            'sort_order' => 2,
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn () => [
            'name' => 'pro',
            'slug' => 'pro',
            'max_products' => null,
            'max_payment_gateways' => null,
            'sort_order' => 3,
        ]);
    }
}
