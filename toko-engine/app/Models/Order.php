<?php

namespace App\Models;

use App\Support\StorefrontContext;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'number', 'demo_store', 'customer_name', 'customer_email', 'customer_phone', 'shipping_address',
    'notes', 'subtotal', 'total', 'status', 'payment_status', 'payment_gateway_code',
    'payment_reference', 'payment_checkout_token', 'payment_checkout_url', 'payment_metadata',
    'public_token', 'paid_at', 'placed_at', 'stock_restored_at',
])]
class Order extends Model
{
    public const STATUSES = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    protected $hidden = ['public_token'];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->public_token ??= Str::random(64);
        });
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'payment_metadata' => 'array',
            'paid_at' => 'datetime',
            'placed_at' => 'datetime',
            'stock_restored_at' => 'datetime',
        ];
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Restrict a query to the demo store an admin account is bound to.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeForAdminStore(Builder $query): void
    {
        $slug = StorefrontContext::adminSlug();

        if ($slug !== null) {
            $query->where('demo_store', $slug);
        }
    }
}
