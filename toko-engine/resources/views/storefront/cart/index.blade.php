<x-layouts::storefront title="Keranjang">
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-18">
        <x-ui.badge variant="orange">Keranjang</x-ui.badge><h1 class="mt-4 text-4xl text-navy sm:text-5xl">Pesananmu</h1>
        @if ($items->isEmpty())
            <x-ui.card class="mt-8 text-center"><p class="text-lg text-navy">Keranjang masih kosong.</p><p class="mt-2 text-sm text-ink-soft">Tambahkan produk favoritmu untuk melanjutkan.</p><div class="mt-6"><x-ui.button :href="route('products.index')">Lihat produk</x-ui.button></div></x-ui.card>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
                <div class="space-y-4">
                    @foreach ($items as $item)
                        <x-ui.card class="flex flex-col gap-5 sm:flex-row sm:items-center">
                            <div class="flex size-20 shrink-0 items-center justify-center rounded-xl bg-tosca-tint text-2xl text-navy/40">{{ \Illuminate\Support\Str::substr($item['product']->name, 0, 1) }}</div>
                            <div class="min-w-0 flex-1"><a href="{{ route('products.show', $item['product']) }}" class="text-lg font-semibold text-navy">{{ $item['product']->name }}</a><p class="mt-1 text-sm text-ink-soft">Rp{{ number_format((float) $item['product']->price, 0, ',', '.') }} / item</p></div>
                            <form method="POST" action="{{ route('cart.update', $item['product']) }}" class="flex items-center gap-2">@csrf @method('PATCH')<input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" max="{{ $item['product']->stock }}" class="w-20 rounded-xl border border-line px-3 py-2"><button class="text-sm font-semibold text-tosca">Update</button></form>
                            <div class="font-semibold text-navy">Rp{{ number_format($item['line_total'], 0, ',', '.') }}</div>
                            <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">@csrf @method('DELETE')<button class="text-sm text-red-600">Hapus</button></form>
                        </x-ui.card>
                    @endforeach
                </div>
                <x-ui.card class="h-fit lg:sticky lg:top-24"><h2 class="text-xl text-navy">Ringkasan</h2><div class="mt-5 flex justify-between border-t border-line pt-5 text-sm text-ink-soft"><span>Subtotal</span><span class="font-semibold text-navy">Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div><p class="mt-3 text-xs leading-5 text-ink-soft">Ongkir belum dihitung pada skeleton ini.</p><div class="mt-6"><x-ui.button :href="route('checkout.create')" class="w-full">Lanjut checkout</x-ui.button></div></x-ui.card>
            </div>
        @endif
    </section>
</x-layouts::storefront>
