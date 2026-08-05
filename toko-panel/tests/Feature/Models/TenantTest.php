<?php

namespace Tests\Feature\Models;

use App\Models\Addon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_exposes_its_operational_relationships(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();
        $historicalSubscription = Subscription::factory()->for($tenant)->for($plan)->create([
            'status' => 'cancelled',
        ]);
        $currentSubscription = Subscription::factory()->for($tenant)->for($plan)->create();

        $invoice = $tenant->invoices()->create([
            'subscription_id' => $currentSubscription->id,
            'invoice_number' => 'INV-TEST-001',
            'status' => 'unpaid',
            'subtotal_platform' => 1500000,
            'subtotal_care' => 250000,
            'total' => 1750000,
            'due_date' => today()->addWeek(),
        ]);
        $invoiceItem = $invoice->items()->create([
            'description' => 'Sewa platform',
            'amount' => 1500000,
        ]);
        $payment = $invoice->payments()->create([
            'gateway' => 'manual',
            'status' => 'pending',
        ]);

        $addon = Addon::create([
            'name' => 'Extra Care',
            'slug' => 'extra-care',
            'price' => 100000,
            'description' => 'Additional operational support.',
        ]);
        $tenantAddon = $tenant->tenantAddons()->create([
            'addon_id' => $addon->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);
        $contentRequest = $tenant->contentChangeRequests()->create([
            'requested_by_user_id' => $tenant->owner_user_id,
            'description' => 'Update hero copy.',
            'status' => 'pending',
            'usage_period_start' => today()->startOfMonth(),
        ]);
        $usage = $tenant->planFeatureUsages()->create([
            'period_start' => today()->startOfMonth(),
            'products_count' => 12,
            'content_requests_used' => 1,
        ]);

        $this->assertTrue($tenant->owner->ownedTenants->contains($tenant));
        $this->assertCount(2, $tenant->subscriptions);
        $this->assertTrue($tenant->subscriptions->contains($historicalSubscription));
        $this->assertTrue($tenant->currentSubscription->is($currentSubscription));
        $this->assertTrue($tenant->invoices->contains($invoice));
        $this->assertTrue($invoice->items->contains($invoiceItem));
        $this->assertTrue($invoice->payments->contains($payment));
        $this->assertTrue($tenant->tenantAddons->contains($tenantAddon));
        $this->assertTrue($tenantAddon->addon->is($addon));
        $this->assertTrue($tenant->contentChangeRequests->contains($contentRequest));
        $this->assertTrue($contentRequest->requestedBy->is($tenant->owner));
        $this->assertTrue($tenant->planFeatureUsages->contains($usage));
    }
}
