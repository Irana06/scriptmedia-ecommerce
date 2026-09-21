<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderStockService;
use Illuminate\Console\Command;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid';

    protected $description = 'Cancel orders left unpaid past the payment window and return their stock to the catalogue';

    public function handle(OrderStockService $stock): int
    {
        $hours = (int) config('store.payment_window_hours', 24);
        $cutoff = now()->subHours($hours);
        $released = 0;

        Order::query()
            ->where('payment_status', 'pending')
            ->whereNot('status', 'cancelled')
            ->whereNull('stock_restored_at')
            ->where('placed_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($stock, &$released): void {
                foreach ($orders as $order) {
                    if ($stock->cancel($order)) {
                        $released++;
                        $this->line("Dibatalkan dan stok dikembalikan: {$order->number}");
                    }
                }
            });

        $this->info($released === 0
            ? 'Tidak ada pesanan kedaluwarsa.'
            : "{$released} pesanan kedaluwarsa dibatalkan, stoknya dikembalikan.");

        return self::SUCCESS;
    }
}
