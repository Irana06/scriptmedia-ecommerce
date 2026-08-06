<?php

namespace Tests\Feature\EndToEnd;

use App\Actions\GenerateRecurringInvoice;
use App\Actions\ProvisionTenant;
use App\Contracts\TenantDatabaseProvisioner;
use App\Jobs\ProvisionTenantJob;
use App\Livewire\Admin\Billing\ManageBilling;
use App\Livewire\Admin\ContentRequests\ManageContentRequests as ManageAdminContentRequests;
use App\Livewire\Admin\Tenants\ManageTenants;
use App\Livewire\Portal\ManageContentRequests as ManageOwnerContentRequests;
use App\Models\ContentChangeRequest;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class PanelWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_tenant_billing_and_content_request_workflow(): void
    {
        $this->travelTo('2026-08-06 09:00:00');
        Queue::fake();
        Notification::fake();
        config(['tenancy.base_domain' => 'shops.test']);

        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();
        $plan = Plan::factory()->create([
            'content_request_quota' => 3,
            'price_platform' => 1500000,
            'price_care_monthly' => 300000,
        ]);

        Livewire::actingAs($admin)
            ->test(ManageTenants::class)
            ->set('name', 'Toko Alur Lengkap')
            ->set('subdomain', 'toko-alur-lengkap')
            ->set('ownerUserId', (string) $owner->id)
            ->set('planId', (string) $plan->id)
            ->set('billingCycle', 'monthly')
            ->call('createTenant')
            ->assertHasNoErrors();

        $tenant = Tenant::query()->where('subdomain', 'toko-alur-lengkap')->sole();
        $subscription = Subscription::query()->where('tenant_id', $tenant->id)->sole();
        $this->assertSame('2026-08-06', $subscription->current_period_start->toDateString());
        $this->assertSame('2026-09-05', $subscription->current_period_end->toDateString());
        $this->assertSame('2026-09-06', $subscription->next_billing_date->toDateString());
        Queue::assertPushed(ProvisionTenantJob::class);

        $provisioner = new class implements TenantDatabaseProvisioner
        {
            public bool $called = false;

            public function provision(Tenant $tenant): void
            {
                $this->called = true;
            }
        };
        (new ProvisionTenant($provisioner))->handle($tenant);

        $this->assertTrue($provisioner->called);
        $this->assertSame('active', $tenant->fresh()->provisioning_status);
        $this->assertDatabaseHas('domains', [
            'tenant_id' => $tenant->id,
            'domain' => 'toko-alur-lengkap.shops.test',
        ]);

        $this->travelTo('2026-09-05 09:00:00');
        $invoice = app(GenerateRecurringInvoice::class)->handle($subscription->fresh());

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame('1800000.00', $invoice->total);
        $this->assertSame('2026-09-06', $invoice->billing_period_start->toDateString());
        $this->assertSame('2026-10-05', $invoice->billing_period_end->toDateString());

        Livewire::actingAs($admin)
            ->test(ManageBilling::class)
            ->call('markPaid', $invoice->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'status' => 'success']);

        $this->travelTo('2026-09-06 09:00:00');
        Livewire::actingAs($owner)
            ->test(ManageOwnerContentRequests::class, ['tenantId' => $tenant->id])
            ->set('description', 'Perbarui banner utama untuk kampanye bulan September.')
            ->call('submit')
            ->assertHasNoErrors();

        $ticket = ContentChangeRequest::query()->where('tenant_id', $tenant->id)->sole();
        $this->assertSame('pending', $ticket->status);
        $this->assertDatabaseHas('plan_feature_usages', [
            'tenant_id' => $tenant->id,
            'content_requests_used' => 1,
        ]);

        Livewire::actingAs($admin)
            ->test(ManageAdminContentRequests::class)
            ->call('updateStatus', $ticket->id, 'in_progress')
            ->assertHasNoErrors()
            ->call('updateStatus', $ticket->id, 'done')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('content_change_requests', [
            'id' => $ticket->id,
            'status' => 'done',
        ]);
    }
}
