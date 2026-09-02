<x-layouts::app title="Portal Tenant">
    <div class="space-y-10">
        <x-ui.section-header
            eyebrow="Portal owner"
            title="Toko milik Anda"
            description="Pantau order, proses pembuatan toko, plan aktif, invoice, dan request perubahan konten."
        />

        <section>
            <div class="flex items-end justify-between gap-4"><div><p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">Order sewa</p><h2 class="mt-2 text-2xl text-navy">Proses pembuatan toko</h2></div><x-ui.button :href="route('home').'#paket'">Pesan toko baru</x-ui.button></div>
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($orders as $order)
                    <x-ui.card class="flex h-full flex-col"><div class="flex items-start justify-between gap-4"><div><p class="text-xs tracking-[0.16em] text-ink-soft uppercase">{{ $order->number }}</p><h3 class="mt-2 text-xl text-navy">{{ $order->business_name }}</h3></div><x-ui.badge :variant="$order->status === 'ready' ? 'tosca' : 'orange'">{{ str($order->status)->replace('_', ' ')->title() }}</x-ui.badge></div><p class="mt-4 text-sm text-ink-soft">{{ str($order->plan->name)->title() }} · Rp{{ number_format((float) $order->amount, 0, ',', '.') }}</p><x-ui.button :href="route('portal.orders.show', $order)" variant="navy" class="mt-6">Lihat detail order</x-ui.button></x-ui.card>
                @empty
                    <x-ui.card class="md:col-span-2 xl:col-span-3"><x-ui.empty-state title="Belum ada order sewa" description="Pilih plan untuk memulai pembuatan toko online." /></x-ui.card>
                @endforelse
            </div>
        </section>

        <section><div class="mb-5"><p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">Toko aktif</p><h2 class="mt-2 text-2xl text-navy">Toko milik Anda</h2></div><div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($tenants as $tenant)
                <x-ui.card class="flex h-full flex-col">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs tracking-[0.18em] text-ink-soft uppercase">{{ $tenant->subdomain }}</p>
                            <h2 class="mt-2 text-2xl text-navy">{{ $tenant->name }}</h2>
                        </div>
                        <x-ui.badge :variant="$tenant->store_status === 'active' ? 'tosca' : 'orange'">
                            {{ str($tenant->store_status)->replace('_', ' ')->title() }}
                        </x-ui.badge>
                    </div>

                    <dl class="mt-6 grid grid-cols-2 gap-4 border-y border-line py-5 text-sm">
                        <div>
                            <dt class="text-ink-soft">Plan aktif</dt>
                            <dd class="mt-1 font-semibold text-navy">{{ $tenant->currentSubscription?->plan?->name ?? 'Belum ada' }}</dd>
                        </div>
                        <div>
                            <dt class="text-ink-soft">Invoice</dt>
                            <dd class="mt-1 font-semibold text-navy">{{ $tenant->invoices_count }}</dd>
                        </div>
                    </dl>

                    <x-ui.button :href="route('portal.tenants.show', $tenant)" variant="navy" class="mt-6" wire:navigate>
                        Buka detail tenant
                    </x-ui.button>
                </x-ui.card>
            @empty
                <x-ui.card class="md:col-span-2 xl:col-span-3">
                    <x-ui.empty-state
                        title="Belum ada toko"
                        description="Tenant yang Anda miliki akan tampil di sini setelah dibuat oleh administrator."
                    />
                </x-ui.card>
            @endforelse
        </div></section>
    </div>
</x-layouts::app>
