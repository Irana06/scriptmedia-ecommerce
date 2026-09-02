<x-layouts::storefront title="Status order">
    <section class="mx-auto max-w-3xl px-5 py-14 sm:px-8 sm:py-20">
        <div class="mb-8">
            <p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">Pelacakan order</p>
            <h1 class="mt-3 text-3xl font-semibold text-navy sm:text-4xl">{{ $order->number }}</h1>
            <p class="mt-3 text-ink-soft">Halo {{ $order->customer_name }}, simpan halaman ini untuk melihat perkembangan pesananmu.</p>
        </div>

        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-offwhite p-4"><p class="text-xs text-ink-soft uppercase">Status order</p><p class="mt-2 font-semibold text-navy">{{ ucfirst($order->status) }}</p></div>
                <div class="rounded-xl bg-offwhite p-4"><p class="text-xs text-ink-soft uppercase">Pembayaran</p><p class="mt-2 font-semibold text-navy">{{ ucfirst($order->payment_status) }}</p></div>
            </div>

            <div class="mt-7 border-t border-line pt-6">
                <h2 class="text-xl font-semibold text-navy">Rincian belanja</h2>
                <div class="mt-4 divide-y divide-line">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between gap-5 py-4 text-sm"><div><p class="font-semibold text-navy">{{ $item->product_name }}</p><p class="mt-1 text-ink-soft">{{ $item->quantity }} × Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}</p></div><p class="font-semibold text-navy">Rp{{ number_format((float) $item->line_total, 0, ',', '.') }}</p></div>
                    @endforeach
                </div>
                <div class="flex justify-between border-t border-line pt-4"><span class="font-semibold text-navy">Total</span><span class="text-lg font-semibold text-navy">Rp{{ number_format((float) $order->total, 0, ',', '.') }}</span></div>
            </div>

            @if ($gateway && $order->payment_status !== 'paid')
                <div class="mt-7 rounded-xl border border-orange/30 bg-orange/10 p-5"><p class="font-semibold text-navy">Pembayaran {{ $gateway->name }}</p><p class="mt-2 text-sm leading-6 text-ink-soft">{{ $gateway->instructions ?: 'Selesaikan pembayaran melalui metode yang dipilih saat checkout.' }}</p></div>
            @endif

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $whatsappTrackingUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-full bg-tosca px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90">Simpan lewat WhatsApp</a>
                <x-ui.button :href="route('products.index')" variant="navy">Kembali belanja</x-ui.button>
            </div>
            <p class="mt-5 text-xs leading-5 text-ink-soft">Jangan bagikan link ini kepada orang lain karena link berfungsi sebagai akses pribadi ke status order.</p>
        </x-ui.card>
    </section>
</x-layouts::storefront>
