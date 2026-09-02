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
            'price_platform' => 150000,
            'price_care_monthly' => 150000,
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
            'price_care_monthly' => 350000,
            'max_products' => 150,
            'max_payment_gateways' => 2,
            'content_request_quota' => 3,
            'support_sla_hours' => 12,
            'custom_domain_allowed' => true,
            'sort_order' => 2,
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn () => [
            'name' => 'pro',
            'slug' => 'pro',
            'price_care_monthly' => 550000,
            'max_products' => null,
            'max_payment_gateways' => null,
            'content_request_quota' => 5,
            'support_sla_hours' => 6,
            'custom_domain_allowed' => true,
            'allow_realtime_shipping' => true,
            'allow_full_design_customization' => true,
            'sort_order' => 3,
        ]);
    }
}
