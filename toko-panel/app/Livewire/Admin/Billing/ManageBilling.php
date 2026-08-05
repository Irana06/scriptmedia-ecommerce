<?php

namespace App\Livewire\Admin\Billing;

use App\Actions\MarkInvoicePaid;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Billing Tenant')]
class ManageBilling extends Component
{
    public string $tenantId = '';

    public function markPaid(int $invoiceId): void
    {
        $invoice = Invoice::query()
            ->whereIn('status', ['unpaid', 'overdue'])
            ->findOrFail($invoiceId);

        app(MarkInvoicePaid::class)->handle($invoice);

        Flux::toast(variant: 'success', text: "Invoice {$invoice->invoice_number} ditandai lunas.");
    }

    public function render(): View
    {
        $tenantId = $this->tenantId !== '' ? (int) $this->tenantId : null;

        return view('livewire.admin.billing.manage-billing', [
            'tenants' => Tenant::query()->orderBy('name')->get(),
            'invoices' => Invoice::query()
                ->with(['tenant', 'subscription.plan', 'items'])
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->latest('due_date')
                ->limit(100)
                ->get(),
            'payments' => Payment::query()
                ->with('invoice.tenant')
                ->when($tenantId, fn ($query) => $query->whereHas(
                    'invoice',
                    fn ($invoiceQuery) => $invoiceQuery->where('tenant_id', $tenantId),
                ))
                ->latest('paid_at')
                ->limit(100)
                ->get(),
        ]);
    }
}
