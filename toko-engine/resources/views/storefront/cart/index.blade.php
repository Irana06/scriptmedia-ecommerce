@php($demoStore = \App\Support\StorefrontContext::store())

<x-layouts::storefront title="Keranjang">
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-18">
        <x-ui.badge variant="orange">Keranjang</x-ui.badge><h1 class="mt-4 text-4xl text-navy sm:text-5xl">Pesananmu</h1>
        @if ($items->isEmpty())
            <x-ui.empty-state class="mt-8" icon="cart" title="Keranjangmu masih kosong" description="Temukan produk favorit, lalu tambahkan ke keranjang untuk melanjutkan pesanan." action-label="Mulai belanja" :action-href="\App\Support\StorefrontContext::route('products.index')" />
        @else
            <p class="mt-4 max-w-2xl text-ink-soft">Centang produk yang mau dipesan sekarang. Produk yang tidak dicentang tetap tersimpan di keranjang.</p>

            <form
                method="POST"
                action="{{ \App\Support\StorefrontContext::route('cart.select') }}"
                class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]"
                x-data="{
                    selected: @js($items->where('selected', true)->pluck('product.id')->map(fn ($id) => (string) $id)->values()),
                    lines: @js($items->mapWithKeys(fn ($item) => [(string) $item['product']->id => $item['line_total']])),
                    get allChecked() { return this.selected.length === Object.keys(this.lines).length },
                    get total() { return this.selected.reduce((sum, id) => sum + (this.lines[id] ?? 0), 0) },
                    toggleAll(checked) { this.selected = checked ? Object.keys(this.lines) : [] },
                    format(value) { return 'Rp' + Math.round(value).toLocaleString('id-ID') },
                }"
            >
                @csrf
                <input type="hidden" name="continue" value="checkout">
                <div class="space-y-4">
                    <label class="flex cursor-pointer items-center gap-3 rounded-card border border-line bg-white px-5 py-4 text-sm font-semibold text-navy">
                        <input type="checkbox" class="size-4 accent-tosca" x-bind:checked="allChecked" x-on:change="toggleAll($event.target.checked)">
                        Pilih semua produk
                        <span class="ml-auto text-xs font-normal text-ink-soft"><span x-text="selected.length"></span> dari {{ $items->count() }} dipilih</span>
                    </label>

                    @foreach ($items as $item)
                        <x-ui.card class="flex flex-col gap-5 sm:flex-row sm:items-center">
                            <input type="checkbox" name="product_ids[]" value="{{ $item['product']->id }}" x-model="selected" class="size-4 shrink-0 accent-tosca" aria-label="Pilih {{ $item['product']->name }}">
                            @php($visual = \App\Support\StorefrontContext::productVisual($item['product']))
                            <div class="flex size-20 shrink-0 items-center justify-center rounded-xl text-2xl text-navy/40" style="background-color: {{ $visual['color'] ?? '#e3f4f3' }}">{{ $visual['icon'] ?? \Illuminate\Support\Str::substr($item['product']->name, 0, 1) }}</div>
                            <div class="min-w-0 flex-1"><a href="{{ \App\Support\StorefrontContext::route('products.show', ['product' => $item['product']]) }}" class="text-lg font-semibold text-navy">{{ $item['product']->name }}</a><p class="mt-1 text-sm text-ink-soft">Rp{{ number_format((float) $item['product']->price, 0, ',', '.') }} / item</p></div>
                            <div class="flex items-center gap-2">
                                <input type="number" form="cart-line-{{ $item['product']->id }}" name="quantity" value="{{ $item['quantity'] }}" min="0" max="{{ $item['product']->stock }}" class="w-20 rounded-xl border border-line px-3 py-2">
                                <x-ui.loading-button form="cart-line-{{ $item['product']->id }}" loading-label="Memperbarui..." variant="outline" class="cursor-pointer !px-4 !py-2 !text-xs">Ubah</x-ui.loading-button>
                            </div>
                            <div class="font-semibold text-navy">Rp{{ number_format($item['line_total'], 0, ',', '.') }}</div>
                            <button type="submit" form="cart-remove-{{ $item['product']->id }}" class="cursor-pointer text-sm text-red-600">Hapus</button>
                        </x-ui.card>
                    @endforeach
                </div>

                <x-ui.card class="h-fit lg:sticky lg:top-24">
                    <h2 class="text-xl text-navy">Ringkasan</h2>
                    <div class="mt-5 flex justify-between border-t border-line pt-5 text-sm text-ink-soft">
                        <span>Subtotal (<span x-text="selected.length"></span> produk)</span>
                        <span class="font-semibold text-navy" x-text="format(total)">Rp{{ number_format($selectedSubtotal, 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-ink-soft">{{ $demoStore['shipping'] ?? 'Biaya pengiriman akan dikonfirmasi oleh toko sesuai alamat tujuan.' }}</p>
                    <div class="mt-6">
                        {{-- A plain button so the disabled state stays bound to the selection. --}}
                        <x-ui.button type="submit" class="w-full cursor-pointer" x-bind:disabled="selected.length === 0">
                            {{ ($demoStore['layout'] ?? null) === 'simple' ? 'Isi data pesanan' : 'Lanjut ke pembayaran' }}
                        </x-ui.button>
                    </div>
                    <p class="mt-3 text-center text-xs text-ink-soft" x-show="selected.length === 0" x-cloak>Pilih minimal satu produk dulu.</p>
                </x-ui.card>
            </form>

            {{-- Kept outside the selection form so nested forms stay valid HTML. --}}
            @foreach ($items as $item)
                <form id="cart-line-{{ $item['product']->id }}" method="POST" action="{{ \App\Support\StorefrontContext::route('cart.update', ['product' => $item['product']]) }}" class="hidden">@csrf @method('PATCH')</form>
                <form id="cart-remove-{{ $item['product']->id }}" method="POST" action="{{ \App\Support\StorefrontContext::route('cart.destroy', ['product' => $item['product']]) }}" class="hidden">@csrf @method('DELETE')</form>
            @endforeach
        @endif
    </section>
</x-layouts::storefront>
