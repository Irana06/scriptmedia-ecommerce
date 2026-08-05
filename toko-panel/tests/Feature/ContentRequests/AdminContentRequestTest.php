<?php

namespace Tests\Feature\ContentRequests;

use App\Livewire\Admin\ContentRequests\ManageContentRequests;
use App\Models\ContentChangeRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminContentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_tickets_by_tenant_and_status(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();
        $firstTenant = Tenant::factory()->create(['owner_user_id' => $owner->id]);
        $secondTenant = Tenant::factory()->create();
        $first = $this->createTicket($firstTenant, $owner, 'pending', 'Ubah headline tenant pertama.');
        $second = $this->createTicket($secondTenant, $secondTenant->owner, 'done', 'Ubah headline tenant kedua.');

        Livewire::actingAs($admin)
            ->test(ManageContentRequests::class)
            ->set('tenantId', (string) $firstTenant->id)
            ->set('status', 'pending')
            ->assertSee($first->description)
            ->assertDontSee($second->description);
    }

    public function test_admin_can_follow_valid_ticket_status_transitions(): void
    {
        $admin = User::factory()->admin()->create();
        $tenant = Tenant::factory()->create();
        $ticket = $this->createTicket($tenant, $tenant->owner, 'pending', 'Ganti gambar hero halaman depan.');

        Livewire::actingAs($admin)
            ->test(ManageContentRequests::class)
            ->call('updateStatus', $ticket->id, 'in_progress')
            ->assertHasNoErrors()
            ->call('updateStatus', $ticket->id, 'done')
            ->assertHasNoErrors()
            ->call('updateStatus', $ticket->id, 'pending')
            ->assertHasErrors(['statusUpdate']);

        $this->assertDatabaseHas('content_change_requests', [
            'id' => $ticket->id,
            'status' => 'done',
        ]);
    }

    public function test_admin_route_is_protected_from_owner_accounts(): void
    {
        $owner = User::factory()->owner()->create();
        Tenant::factory()->create(['owner_user_id' => $owner->id]);

        $this->actingAs($owner)->get(route('admin.content-requests.index'))->assertForbidden();
    }

    private function createTicket(Tenant $tenant, User $requester, string $status, string $description): ContentChangeRequest
    {
        return ContentChangeRequest::query()->create([
            'tenant_id' => $tenant->id,
            'requested_by_user_id' => $requester->id,
            'description' => $description,
            'status' => $status,
            'usage_period_start' => today(),
        ]);
    }
}
