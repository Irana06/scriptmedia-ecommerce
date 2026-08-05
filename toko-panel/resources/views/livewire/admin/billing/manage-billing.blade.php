<div class="space-y-8">
    <x-ui.section-header
        eyebrow="Keuangan tenant"
        title="Billing & Pembayaran"
        description="Pantau invoice recurring, tandai transfer manual, dan lihat riwayat pembayaran per tenant."
    />

    <x-ui.card>
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <x-ui.badge variant="navy">Filter tenant</x-ui.badge>
                <p class="mt-3 text-sm text-ink-soft">Menampilkan maksimal 100 transaksi terbaru.</p>
            </div>
            <div class="w-full sm:max-w-sm">
                <flux:select wire:model.live="tenantId" label="Tenant">
                    <flux:select.option value="">Semua tenant</flux:select.option>
                    @foreach ($tenants as $tenant)
                        <flux:select.option :value="$tenant->id">{{ $tenant->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card :padding="false" class="overflow-hidden">
        <div class="border-b border-line px-6 py-5">
            <h2 class="text-xl text-navy">Invoice</h2>
            <p class="mt-1 text-sm text-ink-soft">Sewa Platform dan Web Care dicatat sebagai item terpisah.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-6xl text-left text-sm">
                <thead class="bg-offwhite text-ink-soft">
                    <tr>
                        <th class="px-6 py-3 font-normal">Invoice</th>
                        <th class="px-6 py-3 font-normal">Tenant</th>
                        <th class="px-6 py-3 font-normal">Periode</th>
                        <th class="px-6 py-3 font-normal">Jatuh tempo</th>
                        <th class="px-6 py-3 font-normal">Total</th>
                        <th class="px-6 py-3 font-normal">Status</th>
                        <th class="px-6 py-3 text-right font-normal">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-white">
                    @forelse ($invoices as $invoice)
                        <tr wire:key="invoice-{{ $invoice->id }}">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-navy">{{ $invoice->invoice_number }}</p>
                                <p class="mt-1 text-xs text-ink-soft">{{ $invoice->items->pluck('description')->join(' + ') }}</p>
                            </td>
                            <td class="px-6 py-4 text-navy">{{ $invoice->tenant->name }}</td>
                            <td class="px-6 py-4 text-ink-soft">
                                @if ($invoice->billing_period_start && $invoice->billing_period_end)
                                    {{ $invoice->billing_period_start->format('d M Y') }} – {{ $invoice->billing_period_end->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-ink-soft">{{ $invoice->due_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-semibold text-navy">Rp{{ number_format((float) $invoice->total, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <x-ui.badge :variant="$invoice->status === 'paid' ? 'tosca' : ($invoice->status === 'overdue' ? 'orange' : 'navy')">
                                    {{ str($invoice->status)->title() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if (in_array($invoice->status, ['unpaid', 'overdue'], true))
                                    <button
                                        type="button"
                                        wire:click="markPaid({{ $invoice->id }})"
                                        wire:confirm="Tandai invoice ini sebagai lunas melalui transfer manual?"
                                        class="rounded-full border border-tosca px-4 py-2 text-xs font-semibold text-navy transition hover:bg-tosca-tint"
                                    >
                                        Tandai lunas
                                    </button>
                                @else
                                    <span class="text-xs text-ink-soft">{{ $invoice->paid_at?->format('d M Y H:i') ?? '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-10 text-center text-ink-soft">Belum ada invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card :padding="false" class="overflow-hidden">
        <div class="border-b border-line px-6 py-5">
            <h2 class="text-xl text-navy">Riwayat pembayaran</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-4xl text-left text-sm">
                <thead class="bg-offwhite text-ink-soft">
                    <tr>
                        <th class="px-6 py-3 font-normal">Waktu</th>
                        <th class="px-6 py-3 font-normal">Tenant</th>
                        <th class="px-6 py-3 font-normal">Invoice</th>
                        <th class="px-6 py-3 font-normal">Metode</th>
                        <th class="px-6 py-3 font-normal">Referensi</th>
                        <th class="px-6 py-3 font-normal">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-white">
                    @forelse ($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}">
                            <td class="px-6 py-4 text-ink-soft">{{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="px-6 py-4 text-navy">{{ $payment->invoice->tenant->name }}</td>
                            <td class="px-6 py-4 text-navy">{{ $payment->invoice->invoice_number }}</td>
                            <td class="px-6 py-4 text-ink-soft">{{ str($payment->gateway)->title() }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-ink-soft">{{ $payment->gateway_reference ?? '—' }}</td>
                            <td class="px-6 py-4"><x-ui.badge variant="tosca">{{ str($payment->status)->title() }}</x-ui.badge></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-ink-soft">Belum ada pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
