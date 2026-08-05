<x-layouts::storefront title="Order berhasil">
    <section class="mx-auto max-w-3xl px-5 py-16 sm:px-8 sm:py-24">
        <x-ui.card class="text-center"><span class="mx-auto flex size-16 items-center justify-center rounded-full bg-tosca-tint text-3xl text-tosca">✓</span><x-ui.badge variant="orange" class="mt-6">Order tercatat</x-ui.badge><h1 class="mt-4 text-4xl text-navy">Terima kasih!</h1><p class="mt-3 text-ink-soft">Nomor order <strong class="text-navy">{{ $order->number }}</strong></p>
            <div class="mt-8 rounded-xl bg-offwhite p-5 text-left"><div class="flex justify-between"><span class="text-ink-soft">Total</span><span class="font-semibold text-navy">Rp{{ number_format((float) $order->total, 0, ',', '.') }}</span></div><div class="mt-3 flex justify-between"><span class="text-ink-soft">Status</span><x-ui.badge>{{ ucfirst($order->status) }}</x-ui.badge></div></div>
            @if ($gateway)<div class="mt-6 rounded-xl border border-orange/30 bg-orange/10 p-5 text-left"><p class="font-semibold text-navy">Instruksi {{ $gateway->name }}</p><p class="mt-2 text-sm leading-6 text-ink-soft">{{ $gateway->instructions }}</p></div>@endif
            <div class="mt-8"><x-ui.button :href="route('products.index')" variant="navy">Lanjut belanja</x-ui.button></div>
        </x-ui.card>
    </section>
</x-layouts::storefront>
