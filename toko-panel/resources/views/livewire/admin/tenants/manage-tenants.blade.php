<div class="space-y-8">
    <x-ui.loading-indicator label="Memperbarui data tenant..." />
    <x-ui.section-header
        eyebrow="Operasional tenant"
        title="Manajemen Tenant"
        description="Tambah tenant, pantau provisioning database, subscription, dan status operasional toko."
    />

    <x-ui.card>
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div>
                <x-ui.badge variant="orange">Auto provisioning</x-ui.badge>
                <h2 class="mt-3 text-2xl text-navy">Tambah tenant baru</h2>
                <p class="mt-1 text-sm text-ink-soft">Database, schema toko-engine, dan domain dibuat otomatis melalui queue.</p>
            </div>
        </div>

        @if ($owners->isEmpty())
            <div class="mt-5 rounded-xl border border-orange/30 bg-orange/10 px-4 py-3 text-sm text-navy">
                Belum ada user dengan role owner. Buat atau seed owner sebelum menambahkan tenant.
            </div>
        @endif

        @if ($plans->isEmpty())
            <div class="mt-5 rounded-xl border border-orange/30 bg-orange/10 px-4 py-3 text-sm text-navy">
                Belum ada plan aktif. Aktifkan atau tambahkan plan sebelum membuat tenant.
            </div>
        @endif

        <form wire:submit="createTenant" class="mt-7 space-y-5">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <flux:input wire:model="name" label="Nama tenant" placeholder="Toko Nusantara" required />
                <flux:input wire:model="subdomain" label="Subdomain" placeholder="toko-nusantara" required />
                <flux:input wire:model="customDomain" label="Custom domain (opsional)" placeholder="tokonusantara.id" />
                <flux:select wire:model="ownerUserId" label="Owner tenant" required>
                    <flux:select.option value="">Pilih owner</flux:select.option>
                    @foreach ($owners as $owner)
                        <flux:select.option :value="$owner->id">{{ $owner->name }} — {{ $owner->email }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="planId" label="Plan" required>
                    <flux:select.option value="">Pilih plan</flux:select.option>
                    @foreach ($plans as $plan)
                        <flux:select.option :value="$plan->id">{{ str($plan->name)->title() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="billingCycle" label="Billing cycle" required>
                    <flux:select.option value="monthly">Bulanan</flux:select.option>
                    <flux:select.option value="annual">Tahunan</flux:select.option>
                </flux:select>
            </div>

            <div class="flex justify-end">
                <x-ui.button type="submit" wire:loading.attr="disabled" :disabled="$owners->isEmpty() || $plans->isEmpty()">
                    <span wire:loading.remove wire:target="createTenant">Simpan &amp; provision tenant</span>
                    <span wire:loading wire:target="createTenant">Menyimpan tenant...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card :padding="false" class="overflow-hidden">
        <div class="flex items-center justify-between gap-4 border-b border-line px-6 py-5">
            <div>
                <h2 class="text-xl text-navy">Daftar tenant</h2>
                <p class="mt-1 text-sm text-ink-soft">{{ $tenants->count() }} tenant terdaftar</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-5xl text-left text-sm">
                <thead class="bg-offwhite text-ink-soft">
                    <tr>
                        <th class="px-6 py-3 font-normal">Tenant</th>
                        <th class="px-6 py-3 font-normal">Owner</th>
                        <th class="px-6 py-3 font-normal">Plan aktif</th>
                        <th class="px-6 py-3 font-normal">Subscription</th>
                        <th class="px-6 py-3 font-normal">Provisioning</th>
                        <th class="px-6 py-3 font-normal">Status toko</th>
                        <th class="px-6 py-3 text-right font-normal">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-white">
                    @forelse ($tenants as $tenant)
                        <tr wire:key="tenant-{{ $tenant->id }}">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-navy">{{ $tenant->name }}</p>
                                <p class="mt-1 text-xs text-ink-soft">{{ $tenant->custom_domain ?? $tenant->subdomain.'.'.config('tenancy.base_domain') }}</p>
                            </td>
                            <td class="px-6 py-4 text-ink-soft">{{ $tenant->owner->name }}</td>
                            <td class="px-6 py-4 text-navy">{{ $tenant->currentSubscription?->plan?->name ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <x-ui.badge :variant="$tenant->currentSubscription?->status === 'active' ? 'tosca' : 'navy'">
                                    {{ str($tenant->currentSubscription?->status ?? 'none')->replace('_', ' ')->title() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :variant="$tenant->provisioning_status === 'failed' ? 'orange' : 'navy'">
                                    {{ str($tenant->provisioning_status)->title() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :variant="$tenant->store_status === 'active' ? 'tosca' : 'orange'">
                                    {{ str($tenant->store_status)->replace('_', ' ')->title() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="toggleStoreStatus({{ $tenant->id }})"
                                    wire:confirm="Ubah status toko tenant ini?"
                                    class="rounded-full border border-line px-4 py-2 text-xs font-semibold text-navy transition hover:border-tosca hover:bg-tosca-tint"
                                >
                                    {{ $tenant->store_status === 'active' ? 'Suspend' : 'Aktifkan' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state
                                    title="Belum ada tenant"
                                    description="Isi formulir di atas untuk membuat tenant dan menjalankan provisioning otomatis."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
