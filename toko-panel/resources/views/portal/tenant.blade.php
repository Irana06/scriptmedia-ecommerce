<x-layouts::app :title="$tenant->name">
    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <a href="{{ route('portal.dashboard') }}" class="text-sm text-tosca hover:text-navy" wire:navigate>← Kembali ke portal</a>
                <h1 class="mt-3 text-3xl text-navy sm:text-4xl">{{ $tenant->name }}</h1>
                <p class="mt-2 text-ink-soft">{{ $tenant->custom_domain ?? $tenant->subdomain.'.example.test' }}</p>
            </div>
            <x-ui.badge :variant="$tenant->store_status === 'active' ? 'tosca' : 'orange'">
                {{ str($tenant->store_status)->replace('_', ' ')->title() }}
            </x-ui.badge>
        </div>

        @if (session('status'))
            <div class="rounded-card border border-tosca/30 bg-tosca-tint px-5 py-4 text-sm text-navy">
                {{ session('status') }}
            </div>
        @endif

        @php($plan = $tenant->currentSubscription?->plan)

        <div class="grid gap-5 lg:grid-cols-3">
            <x-ui.card class="lg:col-span-2">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs tracking-[0.2em] text-tosca uppercase">Plan aktif</p>
                        <h2 class="mt-2 text-2xl text-navy">{{ $plan?->name ? str($plan->name)->title() : 'Belum ada plan' }}</h2>
                    </div>
                    @if ($tenant->currentSubscription)
                        <x-ui.badge variant="navy">{{ str($tenant->currentSubscription->billing_cycle)->title() }}</x-ui.badge>
                    @endif
                </div>

                @if ($plan)
                    <dl class="mt-6 grid gap-4 border-t border-line pt-5 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm text-ink-soft">Maks. produk</dt>
                            <dd class="mt-1 font-semibold text-navy">{{ $plan->max_products ?? 'Tanpa batas' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-ink-soft">Payment gateway</dt>
                            <dd class="mt-1 font-semibold text-navy">{{ $plan->max_payment_gateways ?? 'Tanpa batas' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-ink-soft">Kuota request</dt>
                            <dd class="mt-1 font-semibold text-navy">{{ $plan->content_request_quota }} / periode</dd>
                        </div>
                    </dl>
                @endif
            </x-ui.card>

            <x-ui.card>
                <x-ui.badge variant="orange">Request konten</x-ui.badge>
                <h2 class="mt-4 text-xl text-navy">Ajukan perubahan</h2>
                <form method="POST" action="{{ route('portal.tenants.content-requests.store', $tenant) }}" class="mt-5 space-y-4">
                    @csrf
                    <flux:textarea
                        name="description"
                        label="Deskripsi perubahan"
                        :value="old('description')"
                        placeholder="Jelaskan konten yang perlu diubah..."
                        rows="4"
                        required
                    />
                    <x-ui.button type="submit" class="w-full">Kirim request</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <div class="grid gap-5 xl:grid-cols-2">
            <x-ui.card :padding="false" class="overflow-hidden">
                <div class="border-b border-line px-6 py-5">
                    <h2 class="text-xl text-navy">Histori invoice</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-lg text-left text-sm">
                        <thead class="bg-offwhite text-ink-soft">
                            <tr>
                                <th class="px-6 py-3 font-normal">Nomor</th>
                                <th class="px-6 py-3 font-normal">Jatuh tempo</th>
                                <th class="px-6 py-3 font-normal">Total</th>
                                <th class="px-6 py-3 font-normal">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($tenant->invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 text-navy">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-ink-soft">{{ $invoice->due_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-navy">Rp{{ number_format((float) $invoice->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4"><x-ui.badge variant="navy">{{ str($invoice->status)->title() }}</x-ui.badge></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-ink-soft">Belum ada invoice.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-xl text-navy">Request terbaru</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($tenant->contentChangeRequests as $contentRequest)
                        <article class="rounded-xl border border-line p-4">
                            <div class="flex items-center justify-between gap-3">
                                <time class="text-xs text-ink-soft">{{ $contentRequest->created_at->format('d M Y') }}</time>
                                <x-ui.badge>{{ str($contentRequest->status)->replace('_', ' ')->title() }}</x-ui.badge>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-ink">{{ $contentRequest->description }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-ink-soft">Belum ada request perubahan konten.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts::app>
