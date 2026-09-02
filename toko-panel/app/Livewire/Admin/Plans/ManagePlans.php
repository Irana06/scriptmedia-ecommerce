<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manajemen Plan')]
class ManagePlans extends Component
{
    public ?int $editingPlanId = null;

    public bool $showForm = false;

    public string $name = 'starter';

    public string $slug = '';

    public string $pricePlatform = '0';

    public string $priceCareMonthly = '0';

    public string $maxProducts = '';

    public string $maxPaymentGateways = '';

    public string $contentRequestQuota = '0';

    public string $supportSlaHours = '0';

    public bool $customDomainAllowed = false;

    public bool $allowRealtimeShipping = false;

    public bool $allowFullDesignCustomization = false;

    public bool $isActive = true;

    public string $sortOrder = '0';

    public function createPlan(): void
    {
        $this->resetPlanForm();
        $this->showForm = true;
    }

    public function editPlan(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);

        $this->editingPlanId = $plan->id;
        $this->name = $plan->name;
        $this->slug = $plan->slug;
        $this->pricePlatform = (string) $plan->price_platform;
        $this->priceCareMonthly = (string) $plan->price_care_monthly;
        $this->maxProducts = $plan->max_products === null ? '' : (string) $plan->max_products;
        $this->maxPaymentGateways = $plan->max_payment_gateways === null ? '' : (string) $plan->max_payment_gateways;
        $this->contentRequestQuota = (string) $plan->content_request_quota;
        $this->supportSlaHours = (string) $plan->support_sla_hours;
        $this->customDomainAllowed = $plan->custom_domain_allowed;
        $this->allowRealtimeShipping = $plan->allow_realtime_shipping;
        $this->allowFullDesignCustomization = $plan->allow_full_design_customization;
        $this->isActive = $plan->is_active;
        $this->sortOrder = (string) $plan->sort_order;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function savePlan(): void
    {
        $validated = $this->validate([
            'name' => ['required', Rule::in(['starter', 'standard', 'pro']), Rule::unique('plans', 'name')->ignore($this->editingPlanId)],
            'slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('plans', 'slug')->ignore($this->editingPlanId)],
            'pricePlatform' => ['required', 'numeric', 'min:0'],
            'priceCareMonthly' => ['required', 'numeric', 'min:0'],
            'maxProducts' => ['nullable', 'integer', 'min:0'],
            'maxPaymentGateways' => ['nullable', 'integer', 'min:0'],
            'contentRequestQuota' => ['required', 'integer', 'min:0'],
            'supportSlaHours' => ['required', 'integer', 'min:0'],
            'customDomainAllowed' => ['boolean'],
            'allowRealtimeShipping' => ['boolean'],
            'allowFullDesignCustomization' => ['boolean'],
            'isActive' => ['boolean'],
            'sortOrder' => ['required', 'integer', 'min:0'],
        ]);

        $plan = $this->editingPlanId === null
            ? new Plan
            : Plan::query()->findOrFail($this->editingPlanId);

        $plan->fill([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'price_platform' => $validated['pricePlatform'],
            'price_care_monthly' => $validated['priceCareMonthly'],
            'max_products' => filled($validated['maxProducts']) ? (int) $validated['maxProducts'] : null,
            'max_payment_gateways' => filled($validated['maxPaymentGateways']) ? (int) $validated['maxPaymentGateways'] : null,
            'content_request_quota' => $validated['contentRequestQuota'],
            'support_sla_hours' => $validated['supportSlaHours'],
            'custom_domain_allowed' => $validated['customDomainAllowed'],
            'allow_realtime_shipping' => $validated['allowRealtimeShipping'],
            'allow_full_design_customization' => $validated['allowFullDesignCustomization'],
            'is_active' => $validated['isActive'],
            'sort_order' => $validated['sortOrder'],
        ])->save();

        $this->resetPlanForm();
        Flux::toast(variant: 'success', text: 'Plan berhasil disimpan.');
    }

    public function deletePlan(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);

        if ($plan->subscriptions()->exists() || $plan->pendingSubscriptions()->exists()) {
            $this->addError('deletePlan', 'Plan yang masih digunakan subscription tidak dapat dihapus.');

            return;
        }

        $plan->delete();
        $this->resetPlanForm();
        Flux::toast(variant: 'success', text: 'Plan berhasil dihapus.');
    }

    public function resetPlanForm(): void
    {
        $this->reset([
            'editingPlanId',
            'showForm',
            'slug',
            'maxProducts',
            'maxPaymentGateways',
            'customDomainAllowed',
            'allowRealtimeShipping',
            'allowFullDesignCustomization',
        ]);
        $this->name = 'starter';
        $this->pricePlatform = '0';
        $this->priceCareMonthly = '0';
        $this->contentRequestQuota = '0';
        $this->supportSlaHours = '0';
        $this->isActive = true;
        $this->sortOrder = '0';
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.admin.plans.manage-plans', [
            'plans' => Plan::query()->withCount('subscriptions')->orderBy('sort_order')->get(),
        ]);
    }
}
