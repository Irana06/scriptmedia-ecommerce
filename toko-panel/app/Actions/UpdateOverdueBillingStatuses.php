<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class UpdateOverdueBillingStatuses
{
    /** @return array{overdue_invoices: int, grace_period_tenants: int, suspended_tenants: int} */
    public function handle(): array
    {
        return DB::transaction(function (): array {
            $invoices = Invoice::query()
                ->with(['subscription', 'tenant'])
                ->whereIn('status', ['unpaid', 'overdue'])
                ->whereDate('due_date', '<', today())
                ->lockForUpdate()
                ->get();

            Invoice::query()
                ->whereKey($invoices->modelKeys())
                ->where('status', 'unpaid')
                ->update(['status' => 'overdue']);

            $graceTenants = 0;
            $suspendedTenants = 0;
            $graceDays = (int) config('billing.grace_period_days', 3);
            $suspendDays = (int) config('billing.suspend_after_days', 10);

            foreach ($invoices->groupBy('tenant_id') as $tenantInvoices) {
                $daysOverdue = (int) $tenantInvoices->max(
                    fn (Invoice $invoice): int => (int) $invoice->due_date->diffInDays(today()),
                );
                $targetStatus = match (true) {
                    $daysOverdue >= $suspendDays => 'suspended',
                    $daysOverdue >= $graceDays => 'grace_period',
                    default => null,
                };

                if ($targetStatus === null) {
                    continue;
                }

                Subscription::query()
                    ->whereIn('id', $tenantInvoices->pluck('subscription_id')->unique())
                    ->where('status', '!=', 'cancelled')
                    ->update(['status' => $targetStatus]);

                Tenant::query()
                    ->whereKey($tenantInvoices->first()->tenant_id)
                    ->where('store_status', '!=', 'cancelled')
                    ->update(['store_status' => $targetStatus]);

                $targetStatus === 'suspended' ? $suspendedTenants++ : $graceTenants++;
            }

            return [
                'overdue_invoices' => $invoices->count(),
                'grace_period_tenants' => $graceTenants,
                'suspended_tenants' => $suspendedTenants,
            ];
        });
    }
}
