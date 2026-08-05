<?php

namespace App\Console\Commands;

use App\Actions\GenerateRecurringInvoice;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class GenerateRecurringInvoices extends Command implements Isolatable
{
    protected $signature = 'app:generate-invoices';

    protected $description = 'Generate recurring invoices for subscriptions nearing period end';

    public function handle(GenerateRecurringInvoice $generator): int
    {
        $generated = 0;
        $latestDate = today()->addDays((int) config('billing.invoice_lead_days', 3));

        Subscription::query()
            ->whereIn('status', ['active', 'grace_period'])
            ->whereBetween('current_period_end', [today(), $latestDate])
            ->orderBy('id')
            ->eachById(function (Subscription $subscription) use ($generator, &$generated): void {
                if ($generator->handle($subscription) instanceof Invoice) {
                    $generated++;
                }
            });

        $this->info("{$generated} invoice recurring berhasil dibuat.");

        return self::SUCCESS;
    }
}
