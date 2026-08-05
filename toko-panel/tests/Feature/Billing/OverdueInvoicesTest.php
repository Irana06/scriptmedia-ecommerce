<?php

namespace Tests\Feature\Billing;

use App\Actions\MarkInvoicePaid;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverdueInvoicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_applies_grace_period_and_suspension_thresholds(): void
    {
        config([
            'billing.grace_period_days' => 3,
            'billing.suspend_after_days' => 10,
        ]);
        $plan = Plan::factory()->create();
        $graceTenant = Tenant::factory()->create(['provisioning_status' => 'active']);
        $graceSubscription = Subscription::factory()->for($graceTenant)->for($plan)->create();
        $graceInvoice = Invoice::factory()->for($graceTenant)->for($graceSubscription)->create([
            'due_date' => today()->subDays(3),
        ]);
        $suspendedTenant = Tenant::factory()->create(['provisioning_status' => 'active']);
        $suspendedSubscription = Subscription::factory()->for($suspendedTenant)->for($plan)->create();
        $suspendedInvoice = Invoice::factory()->for($suspendedTenant)->for($suspendedSubscription)->create([
            'due_date' => today()->subDays(10),
        ]);

        $this->artisan('app:check-overdue-invoices')
            ->expectsOutput('2 invoice telat diperiksa.')
            ->expectsOutput('Tenant grace period: 1')
            ->expectsOutput('Tenant suspended: 1')
            ->assertSuccessful();

        $this->assertSame('overdue', $graceInvoice->fresh()->status);
        $this->assertSame('grace_period', $graceSubscription->fresh()->status);
        $this->assertSame('grace_period', $graceTenant->fresh()->store_status);
        $this->assertSame('active', $graceTenant->fresh()->provisioning_status);
        $this->assertSame('overdue', $suspendedInvoice->fresh()->status);
        $this->assertSame('suspended', $suspendedSubscription->fresh()->status);
        $this->assertSame('suspended', $suspendedTenant->fresh()->store_status);
    }

    public function test_manual_payment_is_idempotent_and_restores_billing_status(): void
    {
        $tenant = Tenant::factory()->create(['store_status' => 'suspended']);
        $subscription = Subscription::factory()->for($tenant)->create(['status' => 'suspended']);
        $invoice = Invoice::factory()->for($tenant)->for($subscription)->create([
            'status' => 'overdue',
            'due_date' => today()->subDays(12),
        ]);
        $action = app(MarkInvoicePaid::class);

        $payment = $action->handle($invoice, 'TRANSFER-001');
        $secondPayment = $action->handle($invoice);

        $this->assertTrue($payment->is($secondPayment));
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'gateway' => 'manual',
            'gateway_reference' => 'TRANSFER-001',
            'status' => 'success',
        ]);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame('active', $tenant->fresh()->store_status);
    }
}
