<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            'starter' => [
                'price_care_monthly' => 150000,
                'max_products' => 50,
                'max_payment_gateways' => 1,
                'content_request_quota' => 1,
                'support_sla_hours' => 24,
                'custom_domain_allowed' => false,
                'allow_realtime_shipping' => false,
                'allow_full_design_customization' => false,
                'sort_order' => 1,
            ],
            'standard' => [
                'price_care_monthly' => 350000,
                'max_products' => 150,
                'max_payment_gateways' => 2,
                'content_request_quota' => 3,
                'support_sla_hours' => 12,
                'custom_domain_allowed' => true,
                'allow_realtime_shipping' => false,
                'allow_full_design_customization' => false,
                'sort_order' => 2,
            ],
            'pro' => [
                'price_care_monthly' => 550000,
                'max_products' => null,
                'max_payment_gateways' => null,
                'content_request_quota' => 5,
                'support_sla_hours' => 6,
                'custom_domain_allowed' => true,
                'allow_realtime_shipping' => true,
                'allow_full_design_customization' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $name => $attributes) {
            Plan::updateOrCreate(
                ['slug' => $name],
                [
                    'name' => $name,
                    'price_platform' => 150000,
                    'is_active' => true,
                    ...$attributes,
                ],
            );
        }
    }
}
