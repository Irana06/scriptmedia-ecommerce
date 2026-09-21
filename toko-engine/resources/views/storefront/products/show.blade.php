@php
    $demoStore = \App\Support\StorefrontContext::store();
    $demoLayout = $demoStore['layout'] ?? 'default';
    $hasVariants = $product->hasVariants();
    $detailVisual = \App\Support\StorefrontContext::productVisual($product);
    $dialogBase = [
        'name' => $product->name,
        'icon' => $detailVisual['icon'] ?? mb_substr($product->name, 0, 1),
        'color' => $detailVisual['color'] ?? '#e3f4f3',
        'cartUrl' => \App\Support\StorefrontContext::route('cart.store', ['product' => $product]),
        'buyUrl' => \App\Support\StorefrontContext::route('cart.buy', ['product' => $product]),
    ];
    $variantData = $product->activeVariants()->map(fn ($variant) => [
        'id' => $variant->id,
        'name' => $variant->name,
        'options' => $variant->optionPairs(),
        'price' => (float) $variant->price,
        'stock' => $variant->stock,
    ])->values();
@endphp

<x-layouts::storefront :title="$product->name">
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-18">
        <a href="{{ \App\Support\StorefrontContext::route('products.index', ['category' => $product->category->slug]) }}" class="text-sm font-semibold text-tosca">&larr; Kembali ke katalog</a>
        <div class="mt-6 grid gap-10 lg:grid-cols-2 lg:items-start">
            <x-ui.card :padding="false" class="overflow-hidden">
                @if ($product->getFirstMediaUrl('product-images'))
                    <img src="{{ $product->getFirstMediaUrl('product-images') }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                @elseif ($visual = \App\Support\StorefrontContext::productVisual($product))
                    <div class="flex aspect-square items-center justify-center text-9xl" style="background-color: {{ $visual['color'] }}" role="img" aria-label="Ilustrasi {{ $product->name }}">{{ $visual['icon'] }}</div>
                @else
                    <div class="flex aspect-square items-center justify-center bg-linear-to-br from-tosca-tint to-orange/20 text-9xl text-navy/20">{{ \Illuminate\Support\Str::substr($product->name, 0, 1) }}</div>
                @endif
            </x-ui.card>
            <div
                class="pt-2"
                @if ($hasVariants)
                    x-data="{
                        variants: @js($variantData),
                        base: @js($dialogBase),
                        groups: @js(array_keys($product->optionGroups())),
                        chosen: {},
                        choose(group, value) {
                            this.chosen[group] = this.chosen[group] === value ? undefined : value
                        },
                        /* A value is offered only if some in-stock variant still matches the other picks. */
                        isSelectable(group, value) {
                            return this.variants.some(variant => {
                                if (variant.stock < 1 || variant.options[group] !== value) return false
                                return this.groups.every(other => other === group || ! this.chosen[other] || variant.options[other] === this.chosen[other])
                            })
                        },
                        get variant() {
                            if (this.groups.some(group => ! this.chosen[group])) return null
                            return this.variants.find(v => this.groups.every(g => v.options[g] === this.chosen[g])) ?? null
                        },
                        get canOrder() { return this.variant !== null && this.variant.stock > 0 },
                        get priceLabel() {
                            return this.variant ? this.format(this.variant.price) : @js(number_format($product->displayPrice(), 0, ',', '.') === number_format($product->maxPrice(), 0, ',', '.') ? 'Rp'.number_format($product->displayPrice(), 0, ',', '.') : 'Rp'.number_format($product->displayPrice(), 0, ',', '.').' – Rp'.number_format($product->maxPrice(), 0, ',', '.'))
                        },
                        get stockLabel() {
                            if (! this.variant) return 'Pilih varian untuk melihat ketersediaan.'
                            return this.variant.stock > 0 ? 'Tersedia ' + this.variant.stock + ' item' : 'Varian ini sedang habis'
                        },
                        format(value) { return 'Rp' + Math.round(value).toLocaleString('id-ID') },
                        openDialog(intent) {
                            if (! this.canOrder) return
                            this.$dispatch('open-quantity-dialog', {
                                intent,
                                product: { ...this.base, name: this.base.name + ' — ' + this.variant.name, price: this.variant.price, stock: this.variant.stock, variantId: this.variant.id },
                            })
                        },
                    }"
                @endif
            >
                <x-ui.badge>{{ $product->category->name }}</x-ui.badge>
                <h1 class="mt-5 text-4xl text-navy sm:text-5xl">{{ $product->name }}</h1>
                <p class="mt-5 text-2xl font-semibold text-navy" @if($hasVariants) x-text="priceLabel" @endif>Rp{{ number_format($product->displayPrice(), 0, ',', '.') }}@if ($product->hasPriceRange()) – Rp{{ number_format($product->maxPrice(), 0, ',', '.') }}@endif</p>
                <p class="mt-6 whitespace-pre-line leading-7 text-ink-soft">{{ $product->description }}</p>
                @if ($demoLayout === 'business')
                    <div class="mt-7 grid grid-cols-3 gap-3 text-center text-xs text-ink-soft"><div class="rounded-xl border border-line bg-white p-3">Garansi toko</div><div class="rounded-xl border border-line bg-white p-3">Stok terpantau</div><div class="rounded-xl border border-line bg-white p-3">Bayar fleksibel</div></div>
                @elseif ($demoLayout === 'editorial')
                    <div class="mt-7 border-y border-line py-5 text-sm leading-6 text-ink-soft"><p>Dikurasi oleh Nara Atelier</p><p>Estimasi ongkir mengikuti layanan kurir yang terhubung</p><button type="button" onclick="navigator.share ? navigator.share({ title: @js($product->name), url: location.href }) : navigator.clipboard.writeText(location.href)" class="mt-3 border-b border-current text-navy">Bagikan</button></div>
                @endif
                @if ($hasVariants)
                    <div class="mt-8 grid gap-5">
                        @foreach ($product->optionGroups() as $group => $values)
                            <div>
                                <p class="text-sm font-semibold text-navy">{{ $group }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($values as $value)
                                        <button
                                            type="button"
                                            class="cursor-pointer rounded-full border px-4 py-2 text-sm transition disabled:cursor-not-allowed disabled:opacity-35"
                                            x-on:click="choose(@js($group), @js($value))"
                                            x-bind:disabled="! isSelectable(@js($group), @js($value))"
                                            x-bind:class="chosen[@js($group)] === @js($value) ? 'border-navy bg-navy text-white' : 'border-line bg-white text-navy hover:border-tosca'"
                                        >{{ $value }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 rounded-xl bg-white p-4 text-sm text-ink-soft ring-1 ring-line" x-text="stockLabel"></div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <x-ui.button type="button" variant="outline" class="cursor-pointer" x-bind:disabled="! canOrder" x-on:click="openDialog('cart')">+ Keranjang</x-ui.button>
                        <x-ui.button type="button" variant="navy" class="cursor-pointer" x-bind:disabled="! canOrder" x-on:click="openDialog('buy')">Beli sekarang</x-ui.button>
                    </div>
                    <p class="mt-3 text-xs text-ink-soft" x-show="! variant" x-cloak>Pilih {{ implode(' dan ', array_keys($product->optionGroups())) }} dulu.</p>
                @else
                    <div class="mt-8 rounded-xl bg-white p-4 text-sm text-ink-soft ring-1 ring-line">{{ $product->stock > 0 ? 'Tersedia '.$product->stock.' item' : 'Stok habis' }}</div>
                    @if ($product->stock > 0)
                        <div class="mt-6 flex flex-wrap gap-3" x-data data-product="{{ json_encode($dialogBase + ['price' => (float) $product->price, 'stock' => $product->stock]) }}">
                            <x-ui.button type="button" variant="outline" class="cursor-pointer" x-on:click="$dispatch('open-quantity-dialog', { intent: 'cart', product: JSON.parse($root.dataset.product) })">+ Keranjang</x-ui.button>
                            <x-ui.button type="button" variant="navy" class="cursor-pointer" x-on:click="$dispatch('open-quantity-dialog', { intent: 'buy', product: JSON.parse($root.dataset.product) })">Beli sekarang</x-ui.button>
                        </div>
                    @endif
                @endif
                @if ($demoLayout === 'editorial')
                    @php($isWishlisted = app(\App\Services\WishlistService::class)->contains($product))
                    <form method="POST" action="{{ \App\Support\StorefrontContext::route($isWishlisted ? 'wishlist.destroy' : 'wishlist.store', ['product' => $product]) }}" class="mt-3">@csrf @if($isWishlisted) @method('DELETE') @endif<button type="submit" class="border-b border-current py-2 text-sm font-semibold text-navy">{{ $isWishlisted ? '♥ Hapus dari favorit' : '♡ Simpan ke favorit' }}</button></form>
                @endif
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="border-t border-line bg-white"><div class="mx-auto max-w-7xl px-5 py-16 sm:px-8"><x-ui.section-header eyebrow="Produk terkait" :title="$demoLayout === 'editorial' ? 'Lengkapi ruanganmu' : 'Mungkin kamu juga suka'" /><div class="mt-10 grid gap-6 md:grid-cols-2 {{ $demoLayout === 'editorial' ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">@foreach ($relatedProducts as $relatedProduct)<x-storefront.product-card :product="$relatedProduct" />@endforeach</div></div></section>
    @endif
</x-layouts::storefront>
