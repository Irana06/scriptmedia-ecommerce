<?php

namespace Tests\Feature\Billing;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\RecurringInvoiceGenerated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GenerateRecurringInvoicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_generates_itemized_invoice_and_applies_pending_plan(): void
    {
        Notification::fake();
        config(['billing.invoice_lead_days' => 3]);
        $owner = User::factory()->owner()->create();
        $tenant = Tenant::factory()->for($owner, 'owner')->create();
        $currentPlan = Plan::factory()->create(['name' => 'starter', 'slug' => 'starter-current']);
        $nextPlan = Plan::factory()->pro()->create([
            'slug' => 'pro-next',
            'price_platform' => 2500000,
            'price_care_monthly' => 450000,
        ]);
        $periodEnd = today()->addDays(3);
        $subscription = Subscription::factory()->for($tenant)->for($currentPlan)->create([
            'current_period_start' => $periodEnd->subMonth()->addDay(),
            'current_period_end' => $periodEnd,
            'next_billing_date' => $periodEnd->addDay(),
            'pending_plan_id' => $nextPlan->id,
        ]);

        $this->artisan('app:generate-invoices')
            ->expectsOutput('1 invoice recurring berhasil dibuat.')
            ->assertSuccessful();

        $invoice = Invoice::query()->with('items')->sole();
        $this->assertSame($tenant->id, $invoice->tenant_id);
        $this->assertSame('2500000.00', $invoice->subtotal_platform);
        $this->assertSame('450000.00', $invoice->subtotal_care);
        $this->assertSame('2950000.00', $invoice->total);
        $this->assertSame($periodEnd->addDay()->toDateString(), $invoice->billing_period_start->toDateString());
        $this->assertCount(2, $invoice->items);
        $this->assertTrue($invoice->items->pluck('description')->contains(fn (string $description): bool => str_contains($description, 'Sewa Platform')));
        $this->assertTrue($invoice->items->pluck('description')->contains(fn (string $description): bool => str_contains($description, 'Web Care Bulanan')));

        $subscription->refresh();
        $this->assertSame($nextPlan->id, $subscription->plan_id);
        $this->assertNull($subscription->pending_plan_id);
        Notification::assertSentTo($owner, RecurringInvoiceGenerated::class);

        $this->artisan('app:generate-invoices')
            ->expectsOutput('0 invoice recurring berhasil dibuat.')
            ->assertSuccessful();
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_annual_subscription_charges_ten_months_for_a_twelve_month_period(): void
    {
        Notification::fake();
        $plan = Plan::factory()->create([
            'price_platform' => 150000,
            'price_care_monthly' => 350000,
        ]);
        $subscription = Subscription::factory()->for($plan)->create([
            'billing_cycle' => 'annual',
            'current_period_end' => today()->addDays(3),
        ]);

        $this->artisan('app:generate-invoices')->assertSuccessful();

        $invoice = Invoice::query()->sole();
        $this->assertSame('1500000.00', $invoice->subtotal_platform);
        $this->assertSame('3500000.00', $invoice->subtotal_care);
        $this->assertSame('5000000.00', $invoice->total);
        $this->assertSame(
            $invoice->billing_period_start->addYear()->subDay()->toDateString(),
            $invoice->billing_period_end->toDateString(),
        );
    }
}
