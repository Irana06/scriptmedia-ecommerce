<?php

namespace App\Livewire\Portal;

use App\Actions\CreateContentChangeRequest;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageContentRequests extends Component
{
    #[Locked]
    public int $tenantId;

    public string $description = '';

    public function mount(int $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->ownedTenant();
    }

    public function submit(CreateContentChangeRequest $createContentChangeRequest): void
    {
        $validated = $this->validate([
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $user = auth()->user();
        abort_unless($user instanceof User, 401);

        $createContentChangeRequest->handle(
            $this->ownedTenant(),
            $user,
            (string) $validated['description'],
        );

        $this->reset('description');
        Flux::toast(variant: 'success', text: 'Request perubahan konten berhasil dikirim.');
    }

    public function render(): View
    {
        $tenant = $this->ownedTenant();
        $subscription = Subscription::query()
            ->with('plan')
            ->where('tenant_id', $tenant->getKey())
            ->where('status', 'active')
            ->whereDate('current_period_start', '<=', today())
            ->whereDate('current_period_end', '>=', today())
            ->latest('current_period_start')
            ->first();

        $used = $subscription instanceof Subscription
            ? $tenant->contentChangeRequests()
                ->whereDate('usage_period_start', '>=', $subscription->current_period_start)
                ->whereDate('usage_period_start', '<=', $subscription->current_period_end)
                ->count()
            : 0;

        return view('livewire.portal.manage-content-requests', [
            'contentRequests' => $tenant->contentChangeRequests()->latest()->limit(10)->get(),
            'subscription' => $subscription,
            'used' => $used,
            'quota' => $subscription?->plan->content_request_quota ?? 0,
            'tenantName' => $tenant->name,
        ]);
    }

    private function ownedTenant(): Tenant
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 401);

        return Tenant::query()
            ->whereKey($this->tenantId)
            ->where('owner_user_id', $user->getKey())
            ->firstOrFail();
    }
}
