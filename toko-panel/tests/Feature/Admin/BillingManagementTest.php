<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Billing\ManageBilling;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_open_billing_management(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();

        $this->actingAs($admin)->get(route('admin.billing.index'))->assertOk();
        $this->actingAs($owner)->get(route('admin.billing.index'))->assertForbidden();
    }

    public function test_admin_can_mark_invoice_paid_from_billing_page(): void
    {
        $admin = User::factory()->admin()->create();
        $tenant = Tenant::factory()->create(['store_status' => 'grace_period']);
        $subscription = Subscription::factory()->for($tenant)->create(['status' => 'grace_period']);
        $invoice = Invoice::factory()->for($tenant)->for($subscription)->create([
            'status' => 'overdue',
            'due_date' => today()->subDays(5),
        ]);

        Livewire::actingAs($admin)
            ->test(ManageBilling::class)
            ->assertSee($invoice->invoice_number)
            ->call('markPaid', $invoice->id)
            ->assertHasNoErrors();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'status' => 'success']);
    }

    public function test_billing_commands_are_scheduled_daily(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('app:generate-invoices')
            ->expectsOutputToContain('app:check-overdue-invoices')
            ->assertSuccessful();
    }
}
