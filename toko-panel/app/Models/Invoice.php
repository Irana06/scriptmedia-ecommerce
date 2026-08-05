<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $subscription_id
 * @property string $invoice_number
 * @property string $status
 * @property CarbonInterface|null $billing_period_start
 * @property CarbonInterface|null $billing_period_end
 * @property string $subtotal_platform
 * @property string $subtotal_care
 * @property string $total
 * @property CarbonInterface $due_date
 * @property CarbonInterface|null $paid_at
 */
#[Fillable([
    'tenant_id',
    'subscription_id',
    'invoice_number',
    'status',
    'billing_period_start',
    'billing_period_end',
    'subtotal_platform',
    'subtotal_care',
    'total',
    'due_date',
    'paid_at',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<Subscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /** @return HasMany<InvoiceItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal_platform' => 'decimal:2',
            'subtotal_care' => 'decimal:2',
            'total' => 'decimal:2',
            'billing_period_start' => 'date',
            'billing_period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }
}
