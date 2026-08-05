<?php

namespace App\Livewire\Admin\Tenants;

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manajemen Tenant')]
class ManageTenants extends Component
{
    public string $name = '';

    public string $subdomain = '';

    public string $ownerUserId = '';

    public string $planId = '';

    public string $billingCycle = 'monthly';

    public function createTenant(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'subdomain' => ['required', 'string', 'min:3', 'max:63', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tenants,subdomain'],
            'ownerUserId' => ['required', 'integer', 'exists:users,id'],
            'planId' => ['required', 'integer', 'exists:plans,id'],
            'billingCycle' => ['required', 'in:monthly,annual'],
        ]);

        $owner = User::query()->find((int) $validated['ownerUserId']);
        $plan = Plan::query()->where('is_active', true)->find((int) $validated['planId']);

        if (! $owner?->hasRole(UserRole::Owner->value)) {
            $this->addError('ownerUserId', 'User yang dipilih bukan owner tenant.');

            return;
        }

        if (! $plan instanceof Plan) {
            $this->addError('planId', 'Plan yang dipilih tidak aktif.');

            return;
        }

        $periodStart = today();
        $nextBillingDate = $validated['billingCycle'] === 'annual'
            ? $periodStart->addYear()
            : $periodStart->addMonth();

        DB::transaction(function () use ($validated, $owner, $plan, $periodStart, $nextBillingDate): void {
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'subdomain' => $validated['subdomain'],
                'custom_domain' => null,
                'owner_user_id' => $owner->id,
                'database_name' => 'tenant_'.Str::of((string) $validated['subdomain'])->replace('-', '_'),
                'provisioning_status' => 'pending',
                'store_status' => 'active',
            ]);

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $validated['billingCycle'],
                'status' => 'active',
                'current_period_start' => $periodStart,
                'current_period_end' => $nextBillingDate->subDay(),
                'next_billing_date' => $nextBillingDate,
                'pending_plan_id' => null,
            ]);
        });

        $this->reset(['name', 'subdomain', 'ownerUserId', 'planId']);
        $this->billingCycle = 'monthly';
        Flux::toast(variant: 'success', text: 'Tenant manual berhasil dibuat dengan status pending.');
    }

    public function toggleStoreStatus(int $tenantId): void
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $tenant->update([
            'store_status' => $tenant->store_status === 'active' ? 'suspended' : 'active',
        ]);

        Flux::toast(
            variant: 'success',
            text: $tenant->store_status === 'active' ? 'Tenant berhasil diaktifkan.' : 'Tenant berhasil disuspend.',
        );
    }

    public function render(): View
    {
        return view('livewire.admin.tenants.manage-tenants', [
            'tenants' => Tenant::query()
                ->with(['owner', 'currentSubscription.plan'])
                ->latest()
                ->get(),
            'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'owners' => User::query()
                ->whereHas('roles', fn ($query) => $query
                    ->where('name', UserRole::Owner->value)
                    ->where('guard_name', 'web'))
                ->orderBy('name')
                ->get(),
        ]);
    }
}
