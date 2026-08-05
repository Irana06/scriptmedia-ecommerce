<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'price_platform',
    'price_care_monthly',
    'price_care_annual',
    'max_products',
    'max_payment_gateways',
    'content_request_quota',
    'support_sla_hours',
    'custom_domain_allowed',
    'allow_realtime_shipping',
    'allow_full_design_customization',
    'is_active',
    'sort_order',
])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    /** @return HasMany<Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** @return HasMany<Subscription, $this> */
    public function pendingSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'pending_plan_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_platform' => 'decimal:2',
            'price_care_monthly' => 'decimal:2',
            'price_care_annual' => 'decimal:2',
            'max_products' => 'integer',
            'max_payment_gateways' => 'integer',
            'content_request_quota' => 'integer',
            'support_sla_hours' => 'integer',
            'custom_domain_allowed' => 'boolean',
            'allow_realtime_shipping' => 'boolean',
            'allow_full_design_customization' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
