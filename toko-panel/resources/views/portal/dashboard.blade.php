<x-layouts::app title="Portal Tenant">
    <div class="space-y-10">
        <x-ui.section-header
            eyebrow="Portal owner"
            title="Toko milik Anda"
            description="Lihat plan aktif, invoice, dan request perubahan konten untuk setiap tenant."
        />

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($tenants as $tenant)
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
            @endforeach
        </div>
    </div>
</x-layouts::app>
