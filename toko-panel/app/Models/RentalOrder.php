<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $paid_at
 * @property Carbon|null $credentials_viewed_at
 */
#[Fillable([
    'number', 'user_id', 'plan_id', 'tenant_id', 'billing_cycle', 'business_name',
    'desired_subdomain', 'custom_domain', 'whatsapp', 'notes', 'status', 'amount',
    'payment_gateway', 'payment_reference', 'payment_checkout_token',
    'payment_checkout_url', 'payment_metadata', 'paid_at', 'engine_login_email',
    'engine_temporary_password', 'credentials_viewed_at',
])]
class RentalOrder extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Plan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_metadata' => 'array',
            'paid_at' => 'datetime',
            'engine_temporary_password' => 'encrypted',
            'credentials_viewed_at' => 'datetime',
        ];
    }
}
