<x-layouts::storefront title="Status pesanan">
    <section class="mx-auto max-w-3xl px-5 py-14 sm:px-8 sm:py-20">
        <div class="mb-8">
            <p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">Pelacakan pesanan</p>
            <h1 class="mt-3 text-3xl font-semibold text-navy sm:text-4xl">{{ $order->number }}</h1>
            <p class="mt-3 text-ink-soft">Halo {{ $order->customer_name }}, simpan halaman ini untuk melihat perkembangan pesananmu.</p>
        </div>

        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-offwhite p-4"><p class="text-xs text-ink-soft uppercase">Status pesanan</p><p class="mt-2 font-semibold text-navy">{{ $statusLabel }}</p></div>
                <div class="rounded-xl bg-offwhite p-4"><p class="text-xs text-ink-soft uppercase">Pembayaran</p><p class="mt-2 font-semibold text-navy">{{ $paymentLabel }}</p></div>
            </div>

            <ol class="mt-7 border-t border-line pt-6">
                @foreach ($timeline as $step)
                    <li class="flex gap-4 {{ $loop->last ? '' : 'pb-6' }}">
                        <div class="flex flex-col items-center">
                            <span @class([
                                'flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                                'bg-tosca text-white' => $step['done'],
                                'bg-navy text-white ring-4 ring-navy/15' => ! $step['done'] && $step['current'],
                                'bg-offwhite text-ink-soft ring-1 ring-line' => ! $step['done'] && ! $step['current'],
                            ])>
                                @if ($step['done'])
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </span>
                            @unless ($loop->last)
                                <span class="mt-1 w-px flex-1 {{ $step['done'] ? 'bg-tosca/40' : 'bg-line' }}"></span>
                            @endunless
                        </div>
                        <div class="pt-1">
                            <p class="font-semibold {{ $step['done'] || $step['current'] ? 'text-navy' : 'text-ink-soft' }}">{{ $step['label'] }}</p>
                            <p class="mt-1 text-sm leading-6 text-ink-soft">{{ $step['description'] }}</p>
                            @if ($step['at'])
                                <p class="mt-1 text-xs text-ink-soft">{{ $step['at']->translatedFormat('d M Y, H:i') }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>

            @if ($order->status === 'cancelled')
                <p class="mt-2 rounded-xl border border-orange/30 bg-orange/10 p-4 text-sm leading-6 text-ink-soft">Pesanan ini dibatalkan. Kalau kamu merasa ini keliru, hubungi toko dengan menyebut nomor pesanan di atas.</p>
            @endif

            <div class="mt-7 border-t border-line pt-6">
                <h2 class="text-xl font-semibold text-navy">Rincian belanja</h2>
                <div class="mt-4 divide-y divide-line">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between gap-5 py-4 text-sm"><div><p class="font-semibold text-navy">{{ $item->label() }}</p><p class="mt-1 text-ink-soft">{{ $item->quantity }} × Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}</p></div><p class="font-semibold text-navy">Rp{{ number_format((float) $item->line_total, 0, ',', '.') }}</p></div>
                    @endforeach
                </div>
                <div class="flex justify-between border-t border-line pt-4"><span class="font-semibold text-navy">Total</span><span class="text-lg font-semibold text-navy">Rp{{ number_format((float) $order->total, 0, ',', '.') }}</span></div>
            </div>

            @if ($gateway && $order->payment_status !== 'paid')
                <div class="mt-7 rounded-xl border border-orange/30 bg-orange/10 p-5"><p class="font-semibold text-navy">Pembayaran {{ $gateway->name }}</p><p class="mt-2 text-sm leading-6 text-ink-soft">{{ $gateway->instructions ?: 'Selesaikan pembayaran melalui metode yang dipilih saat checkout.' }}</p></div>
            @endif

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $whatsappTrackingUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-full bg-tosca px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90">Simpan lewat WhatsApp</a>
                <x-ui.button :href="\App\Support\StorefrontContext::route('products.index')" variant="navy">Kembali belanja</x-ui.button>
            </div>
            <p class="mt-5 text-xs leading-5 text-ink-soft">Jangan bagikan link ini kepada orang lain karena link berfungsi sebagai akses pribadi ke status pesanan.</p>
        </x-ui.card>
    </section>
</x-layouts::storefront>
