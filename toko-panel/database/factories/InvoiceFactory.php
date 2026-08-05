<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $periodStart = today()->addMonth()->startOfMonth();

        return [
            'tenant_id' => fn (array $attributes): int => (int) Subscription::query()
                ->whereKey((int) $attributes['subscription_id'])
                ->value('tenant_id'),
            'subscription_id' => Subscription::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('##########'),
            'status' => 'unpaid',
            'billing_period_start' => $periodStart,
            'billing_period_end' => $periodStart->addMonth()->subDay(),
            'subtotal_platform' => 1500000,
            'subtotal_care' => 250000,
            'total' => 1750000,
            'due_date' => $periodStart,
            'paid_at' => null,
        ];
    }
}
