<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarkInvoicePaid
{
    public function handle(Invoice $invoice, ?string $reference = null): Payment
    {
        return DB::transaction(function () use ($invoice, $reference): Payment {
            $lockedInvoice = Invoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $existingPayment = $lockedInvoice->payments()->where('status', 'success')->latest()->first();

            if ($lockedInvoice->status === 'paid' && $existingPayment instanceof Payment) {
                return $existingPayment;
            }

            $paidAt = now();
            $payment = $lockedInvoice->payments()->create([
                'gateway' => 'manual',
                'gateway_reference' => $reference ?: 'MAN-'.Str::upper(Str::uuid()->toString()),
                'status' => 'success',
                'paid_at' => $paidAt,
            ]);
            $lockedInvoice->update([
                'status' => 'paid',
                'paid_at' => $paidAt,
            ]);

            $hasOtherOverdueInvoices = Invoice::query()
                ->where('tenant_id', $lockedInvoice->tenant_id)
                ->whereKeyNot($lockedInvoice->getKey())
                ->whereIn('status', ['unpaid', 'overdue'])
                ->whereDate('due_date', '<', today())
                ->exists();

            if (! $hasOtherOverdueInvoices) {
                Subscription::query()
                    ->whereKey($lockedInvoice->subscription_id)
                    ->whereIn('status', ['grace_period', 'suspended'])
                    ->update(['status' => 'active']);
                Tenant::query()
                    ->whereKey($lockedInvoice->tenant_id)
                    ->whereIn('store_status', ['grace_period', 'suspended'])
                    ->update(['store_status' => 'active']);
            }

            return $payment;
        });
    }
}
