<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name',
    'subdomain',
    'custom_domain',
    'owner_user_id',
    'database_name',
    'provisioning_status',
    'store_status',
])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return HasOne<Subscription, $this> */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /** @return HasMany<Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** @return HasMany<TenantAddon, $this> */
    public function tenantAddons(): HasMany
    {
        return $this->hasMany(TenantAddon::class);
    }

    /** @return HasMany<ContentChangeRequest, $this> */
    public function contentChangeRequests(): HasMany
    {
        return $this->hasMany(ContentChangeRequest::class);
    }

    /** @return HasMany<PlanFeatureUsage, $this> */
    public function planFeatureUsages(): HasMany
    {
        return $this->hasMany(PlanFeatureUsage::class);
    }
}
