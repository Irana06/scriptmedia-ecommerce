<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Orders placed before the store column existed still belong to a storefront.
     * Demo products carry their store as a slug prefix, so the order's own items
     * say where it was placed.
     */
    public function up(): void
    {
        foreach (array_keys((array) config('demo-stores')) as $slug) {
            DB::table('orders')
                ->whereNull('demo_store')
                ->whereExists(function ($query) use ($slug): void {
                    $query->select(DB::raw(1))
                        ->from('order_items')
                        ->join('products', 'products.id', '=', 'order_items.product_id')
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->where('products.slug', 'like', $slug.'-%');
                })
                ->update(['demo_store' => $slug]);
        }
    }

    public function down(): void
    {
        // Leaving the backfilled values in place is harmless.
    }
};
