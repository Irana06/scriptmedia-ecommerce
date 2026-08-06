<x-ui.card>
    <x-ui.loading-indicator label="Mengirim request konten..." target="submit" />
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <x-ui.badge variant="orange">Request konten</x-ui.badge>
            <h2 class="mt-4 text-xl text-navy">Ajukan perubahan</h2>
            @if ($subscription)
                <p class="mt-2 text-sm text-ink-soft">
                    Periode {{ $subscription->current_period_start->format('d M Y') }}–{{ $subscription->current_period_end->format('d M Y') }}
                </p>
            @endif
        </div>

        @if ($subscription)
            <div class="rounded-xl bg-offwhite px-4 py-3 text-right">
                <p class="text-xs tracking-wide text-ink-soft uppercase">Kuota terpakai</p>
                <p class="mt-1 text-xl font-semibold text-navy">{{ $used }} / {{ $quota }}</p>
            </div>
        @endif
    </div>

    @if (! $subscription)
        <div class="mt-5 rounded-xl border border-orange/40 bg-orange/10 px-4 py-3 text-sm text-navy">
            Tenant belum memiliki subscription aktif pada periode ini.
        </div>
    @elseif ($used >= $quota)
        <div class="mt-5 rounded-xl border border-orange/40 bg-orange/10 px-4 py-4 text-sm text-navy">
            <p>Kuota request konten periode ini sudah habis. Upgrade plan untuk memperoleh kuota tambahan.</p>
            <x-ui.button
                class="mt-4"
                variant="navy"
                :href="'mailto:'.config('mail.from.address').'?subject='.rawurlencode('Upgrade plan '.$tenantName)"
            >
                Upgrade plan
            </x-ui.button>
        </div>
    @else
        <form wire:submit="submit" class="mt-5 space-y-4">
            <flux:textarea
                wire:model="description"
                label="Deskripsi perubahan"
                placeholder="Jelaskan konten yang perlu diubah..."
                rows="4"
                required
            />
            <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit">Kirim request</span>
                <span wire:loading wire:target="submit">Mengirim...</span>
            </x-ui.button>
        </form>
    @endif

    <div class="mt-7 border-t border-line pt-6">
        <h3 class="text-lg text-navy">Request terbaru</h3>
        <div class="mt-4 space-y-3">
            @forelse ($contentRequests as $contentRequest)
                <article class="rounded-xl border border-line p-4" wire:key="content-request-{{ $contentRequest->id }}">
                    <div class="flex items-center justify-between gap-3">
                        <time class="text-xs text-ink-soft">{{ $contentRequest->created_at->format('d M Y H:i') }}</time>
                        <x-ui.badge :variant="match ($contentRequest->status) { 'done' => 'tosca', 'rejected' => 'danger', 'in_progress' => 'navy', default => 'orange' }">
                            {{ str($contentRequest->status)->replace('_', ' ')->title() }}
                        </x-ui.badge>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-ink">{{ $contentRequest->description }}</p>
                </article>
            @empty
                <x-ui.empty-state
                    class="py-4"
                    title="Belum ada request"
                    description="Request perubahan konten yang Anda kirim akan tampil di sini."
                />
            @endforelse
        </div>
    </div>
</x-ui.card>
