<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\InitializationHelpers;
use Stancl\Tenancy\Database\Concerns\InvalidatesResolverCache;
use Stancl\Tenancy\Database\Concerns\TenantRun;
use Stancl\Tenancy\Database\Contracts\TenantWithDatabase;

#[Fillable([
    'name',
    'subdomain',
    'custom_domain',
    'owner_user_id',
    'database_name',
    'provisioning_status',
    'store_status',
])]
class Tenant extends Model implements TenantContract, TenantWithDatabase
{
    use CentralConnection;
    use HasDatabase;
    use HasDomains;

    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    use InitializationHelpers;
    use InvalidatesResolverCache;
    use TenantRun;

    public function getTenantKeyName(): string
    {
        return $this->getKeyName();
    }

    public function getTenantKey(): int|string
    {
        return $this->getKey();
    }

    public function getInternal(string $key): mixed
    {
        return $key === 'db_name'
            ? $this->database_name
            : $this->getAttribute('tenancy_'.$key);
    }

    public function setInternal(string $key, mixed $value): static
    {
        $this->setAttribute($key === 'db_name' ? 'database_name' : 'tenancy_'.$key, $value);

        return $this;
    }

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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['owner_user_id' => 'integer'];
    }
}
