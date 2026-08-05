<?php

namespace App\Livewire\Admin\Tenants;

use App\Enums\UserRole;
use App\Jobs\ProvisionTenantJob;
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

    public string $customDomain = '';

    public string $ownerUserId = '';

    public string $planId = '';

    public string $billingCycle = 'monthly';

    public function createTenant(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'subdomain' => ['required', 'string', 'min:3', 'max:63', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tenants,subdomain'],
            'customDomain' => ['nullable', 'string', 'max:253', 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', 'unique:tenants,custom_domain'],
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

        $customDomain = filled($validated['customDomain'])
            ? strtolower((string) $validated['customDomain'])
            : null;

        if ($customDomain !== null && ! $plan->custom_domain_allowed) {
            $this->addError('customDomain', 'Plan yang dipilih belum mendukung custom domain.');

            return;
        }

        $periodStart = today();
        $nextBillingDate = $validated['billingCycle'] === 'annual'
            ? $periodStart->addYear()
            : $periodStart->addMonth();

        $tenant = DB::transaction(function () use ($validated, $customDomain, $owner, $plan, $periodStart, $nextBillingDate): Tenant {
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'subdomain' => $validated['subdomain'],
                'custom_domain' => $customDomain,
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

            return $tenant;
        });

        ProvisionTenantJob::dispatch($tenant->id)->afterCommit();

        $this->reset(['name', 'subdomain', 'customDomain', 'ownerUserId', 'planId']);
        $this->billingCycle = 'monthly';
        Flux::toast(variant: 'success', text: 'Tenant dibuat. Provisioning database sedang diproses.');
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
