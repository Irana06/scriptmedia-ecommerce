<div class="space-y-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-semibold tracking-[0.22em] text-tosca uppercase">Konfigurasi paket</p>
            <h1 class="mt-2 text-3xl text-navy sm:text-4xl">Manajemen Plan</h1>
            <p class="mt-2 max-w-2xl text-ink-soft">Harga dan seluruh limit fitur dibaca langsung dari record plan.</p>
        </div>
        <x-ui.button wire:click="createPlan">Tambah plan</x-ui.button>
    </div>

    @error('deletePlan')
        <div class="rounded-card border border-orange/30 bg-orange/10 px-5 py-4 text-sm text-navy">{{ $message }}</div>
    @enderror

    @if ($showForm)
        <x-ui.card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <x-ui.badge variant="navy">{{ $editingPlanId ? 'Edit plan' : 'Plan baru' }}</x-ui.badge>
                    <h2 class="mt-3 text-2xl text-navy">{{ $editingPlanId ? 'Perbarui konfigurasi plan' : 'Tambahkan plan' }}</h2>
                </div>
                <button type="button" wire:click="resetPlanForm" class="text-sm text-ink-soft hover:text-navy">Tutup</button>
            </div>

            <form wire:submit="savePlan" class="mt-7 space-y-7">
                <div class="grid gap-5 md:grid-cols-3">
                    <flux:select wire:model="name" label="Tier" required>
                        <flux:select.option value="starter">Starter</flux:select.option>
                        <flux:select.option value="standard">Standard</flux:select.option>
                        <flux:select.option value="pro">Pro</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="slug" label="Slug" placeholder="starter" required />
                    <flux:input wire:model="sortOrder" label="Urutan" type="number" min="0" required />
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-navy">Harga</h3>
                    <div class="mt-3 grid gap-5 md:grid-cols-3">
                        <flux:input wire:model="pricePlatform" label="Sewa platform" type="number" min="0" step="0.01" required />
                        <flux:input wire:model="priceCareMonthly" label="Care bulanan" type="number" min="0" step="0.01" required />
                        <flux:input wire:model="priceCareAnnual" label="Care tahunan" type="number" min="0" step="0.01" required />
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-navy">Limit fitur</h3>
                    <p class="mt-1 text-xs text-ink-soft">Kosongkan produk atau payment gateway untuk nilai unlimited.</p>
                    <div class="mt-3 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        <flux:input wire:model="maxProducts" label="Maks. produk" type="number" min="0" />
                        <flux:input wire:model="maxPaymentGateways" label="Maks. gateway" type="number" min="0" />
                        <flux:input wire:model="contentRequestQuota" label="Kuota request konten" type="number" min="0" required />
                        <flux:input wire:model="supportSlaHours" label="SLA support (jam)" type="number" min="0" required />
                    </div>
                </div>

                <div class="grid gap-4 rounded-card bg-offwhite p-5 md:grid-cols-2">
                    <flux:checkbox wire:model="customDomainAllowed" label="Custom domain diizinkan" />
                    <flux:checkbox wire:model="allowRealtimeShipping" label="Realtime shipping diizinkan" />
                    <flux:checkbox wire:model="allowFullDesignCustomization" label="Full design customization" />
                    <flux:checkbox wire:model="isActive" label="Plan aktif" />
                </div>

                <div class="flex flex-wrap justify-end gap-3">
                    <x-ui.button type="button" variant="navy" wire:click="resetPlanForm">Batal</x-ui.button>
                    <x-ui.button type="submit">Simpan plan</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    <div class="grid gap-5 xl:grid-cols-3">
        @forelse ($plans as $plan)
            <x-ui.card class="flex h-full flex-col">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs tracking-[0.18em] text-ink-soft uppercase">{{ $plan->slug }}</p>
                        <h2 class="mt-2 text-2xl text-navy">{{ str($plan->name)->title() }}</h2>
                    </div>
                    <x-ui.badge :variant="$plan->is_active ? 'tosca' : 'navy'">{{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                </div>

                <div class="mt-5 rounded-xl bg-offwhite p-4">
                    <p class="text-sm text-ink-soft">Web Care bulanan</p>
                    <p class="mt-1 text-2xl text-navy">Rp{{ number_format((float) $plan->price_care_monthly, 0, ',', '.') }}</p>
                </div>

                <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div><dt class="text-ink-soft">Produk</dt><dd class="mt-1 text-navy">{{ $plan->max_products ?? 'Unlimited' }}</dd></div>
                    <div><dt class="text-ink-soft">Gateway</dt><dd class="mt-1 text-navy">{{ $plan->max_payment_gateways ?? 'Unlimited' }}</dd></div>
                    <div><dt class="text-ink-soft">Request konten</dt><dd class="mt-1 text-navy">{{ $plan->content_request_quota }}</dd></div>
                    <div><dt class="text-ink-soft">SLA</dt><dd class="mt-1 text-navy">{{ $plan->support_sla_hours }} jam</dd></div>
                </dl>

                <p class="mt-5 text-xs text-ink-soft">{{ $plan->subscriptions_count }} subscription</p>
                <div class="mt-auto flex gap-3 pt-6">
                    <x-ui.button variant="navy" class="flex-1" wire:click="editPlan({{ $plan->id }})">Edit</x-ui.button>
                    <button
                        type="button"
                        wire:click="deletePlan({{ $plan->id }})"
                        wire:confirm="Hapus plan ini?"
                        class="rounded-full border border-line px-5 py-3 text-sm font-semibold text-ink-soft transition hover:border-orange hover:text-navy"
                    >
                        Hapus
                    </button>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card class="xl:col-span-3"><p class="text-center text-ink-soft">Belum ada plan.</p></x-ui.card>
        @endforelse
    </div>
</div>
