<?php

namespace App\Console\Commands;

use App\Actions\UpdateOverdueBillingStatuses;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class CheckOverdueInvoices extends Command implements Isolatable
{
    protected $signature = 'app:check-overdue-invoices';

    protected $description = 'Mark overdue invoices and apply grace period or suspension';

    public function handle(UpdateOverdueBillingStatuses $updater): int
    {
        $result = $updater->handle();

        $this->info("{$result['overdue_invoices']} invoice telat diperiksa.");
        $this->line("Tenant grace period: {$result['grace_period_tenants']}");
        $this->line("Tenant suspended: {$result['suspended_tenants']}");

        return self::SUCCESS;
    }
}
