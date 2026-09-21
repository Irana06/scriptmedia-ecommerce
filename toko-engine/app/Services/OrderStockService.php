<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderStockService
{
    /**
     * Put an order's reserved stock back on the shelf.
     *
     * Stock is taken when the order is placed, so an order that is cancelled or
     * left unpaid would otherwise hold that stock forever. Returns true when this
     * call is the one that released it.
     */
    public function release(Order $order): bool
    {
        return DB::transaction(function () use ($order): bool {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($locked->stock_restored_at !== null || $locked->payment_status === 'paid') {
                return false;
            }

            foreach ($locked->items()->get() as $item) {
                // Stock was taken from the variant when the order carried one.
                if ($item->product_variant_id !== null) {
                    ProductVariant::query()->whereKey($item->product_variant_id)->increment('stock', $item->quantity);

                    continue;
                }

                if ($item->product_id === null) {
                    continue;
                }

                Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
            }

            $locked->forceFill(['stock_restored_at' => now()])->save();
            $order->setRawAttributes($locked->getAttributes());

            return true;
        });
    }

    /** Cancel an order and return its stock in one step. */
    public function cancel(Order $order, string $paymentStatus = 'failed'): bool
    {
        $released = $this->release($order);

        $order->forceFill([
            'status' => 'cancelled',
            'payment_status' => $order->payment_status === 'paid' ? $order->payment_status : $paymentStatus,
        ])->save();

        return $released;
    }
}
