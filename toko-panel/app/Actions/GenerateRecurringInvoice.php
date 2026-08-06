<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\RecurringInvoiceGenerated;
use Illuminate\Support\Facades\DB;

class GenerateRecurringInvoice
{
    public function handle(Subscription $subscription): ?Invoice
    {
        $invoice = DB::transaction(function () use ($subscription): ?Invoice {
            $lockedSubscription = Subscription::query()
                ->with(['plan', 'pendingPlan', 'tenant.owner'])
                ->lockForUpdate()
                ->find($subscription->getKey());

            if (! $lockedSubscription instanceof Subscription || ! $this->isEligible($lockedSubscription)) {
                return null;
            }

            $periodStart = $lockedSubscription->current_period_end->copy()->addDay();

            if (Invoice::query()
                ->where('subscription_id', $lockedSubscription->getKey())
                ->whereDate('billing_period_start', $periodStart)
                ->exists()) {
                return null;
            }

            $plan = $lockedSubscription->pendingPlan ?? $lockedSubscription->plan;

            if (! $plan instanceof Plan) {
                return null;
            }

            $periodEnd = $lockedSubscription->billing_cycle === 'annual'
                ? $periodStart->copy()->addYear()->subDay()
                : $periodStart->copy()->addMonth()->subDay();
            $carePrice = $lockedSubscription->billing_cycle === 'annual'
                ? (float) $plan->price_care_annual
                : (float) $plan->price_care_monthly;
            $platformPrice = (float) $plan->price_platform;

            $invoice = Invoice::create([
                'tenant_id' => $lockedSubscription->tenant_id,
                'subscription_id' => $lockedSubscription->getKey(),
                'invoice_number' => $this->invoiceNumber($lockedSubscription, $periodStart->format('Ym')),
                'status' => 'unpaid',
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'subtotal_platform' => $platformPrice,
                'subtotal_care' => $carePrice,
                'total' => $platformPrice + $carePrice,
                'due_date' => $periodStart,
                'paid_at' => null,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->getKey(),
                'description' => 'Sewa Platform — Plan '.str($plan->name)->title(),
                'amount' => $platformPrice,
            ]);
            InvoiceItem::create([
                'invoice_id' => $invoice->getKey(),
                'description' => 'Web Care '.($lockedSubscription->billing_cycle === 'annual' ? 'Tahunan' : 'Bulanan').' — Plan '.str($plan->name)->title(),
                'amount' => $carePrice,
            ]);

            $lockedSubscription->update([
                'plan_id' => $plan->getKey(),
                'pending_plan_id' => null,
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'next_billing_date' => $periodEnd->copy()->addDay(),
            ]);

            return $invoice;
        });

        if ($invoice instanceof Invoice) {
            $invoice->loadMissing(['tenant.owner', 'items']);
            $owner = $invoice->tenant->owner;

            if ($owner instanceof User) {
                $owner->notify(new RecurringInvoiceGenerated($invoice));
            }
        }

        return $invoice;
    }

    private function isEligible(Subscription $subscription): bool
    {
        if (! in_array($subscription->status, ['active', 'grace_period'], true)) {
            return false;
        }

        $today = today();
        $latestDate = $today->copy()->addDays((int) config('billing.invoice_lead_days', 3));

        return ! $subscription->current_period_end->isBefore($today)
            && ! $subscription->current_period_end->isAfter($latestDate);
    }

    private function invoiceNumber(Subscription $subscription, string $period): string
    {
        return sprintf('INV-%s-T%06d-S%06d', $period, $subscription->tenant_id, $subscription->getKey());
    }
}
