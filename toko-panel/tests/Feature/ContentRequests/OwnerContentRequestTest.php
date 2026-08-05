<?php

namespace Tests\Feature\ContentRequests;

use App\Livewire\Portal\ManageContentRequests;
use App\Models\ContentChangeRequest;
use App\Models\Plan;
use App\Models\PlanFeatureUsage;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerContentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_submit_until_current_subscription_quota_is_full(): void
    {
        $this->travelTo('2026-08-05 10:00:00');

        $owner = User::factory()->owner()->create();
        $tenant = Tenant::factory()->create(['owner_user_id' => $owner->id]);
        $plan = Plan::factory()->create(['content_request_quota' => 2]);
        Subscription::factory()->for($tenant)->for($plan)->create([
            'current_period_start' => '2026-07-20',
            'current_period_end' => '2026-08-19',
            'next_billing_date' => '2026-08-20',
        ]);

        $component = Livewire::actingAs($owner)
            ->test(ManageContentRequests::class, ['tenantId' => $tenant->id])
            ->set('description', 'Perbarui judul utama pada halaman depan.')
            ->call('submit')
            ->assertHasNoErrors()
            ->set('description', 'Ganti foto banner promosi di halaman depan.')
            ->call('submit')
            ->assertHasNoErrors();

        $component
            ->set('description', 'Ubah teks tombol katalog menjadi lihat produk.')
            ->call('submit')
            ->assertHasErrors(['description'])
            ->assertSee('Kuota request konten periode ini sudah habis');

        $this->assertDatabaseCount('content_change_requests', 2);
        $this->assertSame(2, PlanFeatureUsage::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('period_start', '2026-07-20')
            ->value('content_requests_used'));
    }

    public function test_usage_starts_from_zero_in_a_new_subscription_period(): void
    {
        $this->travelTo('2026-08-05 10:00:00');

        $owner = User::factory()->owner()->create();
        $tenant = Tenant::factory()->create(['owner_user_id' => $owner->id]);
        $plan = Plan::factory()->create(['content_request_quota' => 1]);
        Subscription::factory()->for($tenant)->for($plan)->create([
            'current_period_start' => '2026-08-01',
            'current_period_end' => '2026-08-31',
            'next_billing_date' => '2026-09-01',
        ]);
        PlanFeatureUsage::query()->create([
            'tenant_id' => $tenant->id,
            'period_start' => '2026-07-01',
            'products_count' => 0,
            'content_requests_used' => 1,
        ]);
        ContentChangeRequest::query()->create([
            'tenant_id' => $tenant->id,
            'requested_by_user_id' => $owner->id,
            'description' => 'Permintaan dari periode sebelumnya.',
            'status' => 'done',
            'usage_period_start' => '2026-07-01',
            'created_at' => '2026-07-10 10:00:00',
            'updated_at' => '2026-07-10 10:00:00',
        ]);

        Livewire::actingAs($owner)
            ->test(ManageContentRequests::class, ['tenantId' => $tenant->id])
            ->set('description', 'Perbarui banner untuk periode langganan baru.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(1, PlanFeatureUsage::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('period_start', '2026-08-01')
            ->value('content_requests_used'));
        $this->assertDatabaseCount('content_change_requests', 2);
    }

    public function test_owner_cannot_mount_another_tenants_request_component(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = Tenant::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($owner)
            ->test(ManageContentRequests::class, ['tenantId' => $tenant->id]);
    }
}
