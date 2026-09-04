@php
    $demoStore = \App\Support\StorefrontContext::store();
    $demoLayout = $demoStore['layout'] ?? 'default';
@endphp

<x-layouts::storefront :title="$product->name">
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-18">
        <a href="{{ \App\Support\StorefrontContext::route('products.index', ['category' => $product->category->slug]) }}" class="text-sm font-semibold text-tosca">&larr; Kembali ke katalog</a>
        <div class="mt-6 grid gap-10 lg:grid-cols-2 lg:items-start">
            <x-ui.card :padding="false" class="overflow-hidden">
                @if ($product->getFirstMediaUrl('product-images'))
                    <img src="{{ $product->getFirstMediaUrl('product-images') }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                @else
                    <div class="flex aspect-square items-center justify-center bg-linear-to-br from-tosca-tint to-orange/20 text-9xl text-navy/20">{{ \Illuminate\Support\Str::substr($product->name, 0, 1) }}</div>
                @endif
            </x-ui.card>
            <div class="pt-2">
                <x-ui.badge>{{ $product->category->name }}</x-ui.badge>
                <h1 class="mt-5 text-4xl text-navy sm:text-5xl">{{ $product->name }}</h1>
                <p class="mt-5 text-2xl font-semibold text-navy">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p>
                <p class="mt-6 whitespace-pre-line leading-7 text-ink-soft">{{ $product->description }}</p>
                @if ($demoLayout === 'business')
                    <div class="mt-7 grid grid-cols-3 gap-3 text-center text-xs text-ink-soft"><div class="rounded-xl border border-line bg-white p-3">Garansi toko</div><div class="rounded-xl border border-line bg-white p-3">Stok terpantau</div><div class="rounded-xl border border-line bg-white p-3">Bayar fleksibel</div></div>
                @elseif ($demoLayout === 'editorial')
                    <div class="mt-7 border-y border-line py-5 text-sm leading-6 text-ink-soft"><p>Curated by Nara Atelier</p><p>Estimasi real-time mengikuti integrasi kurir yang diaktifkan</p><button type="button" onclick="navigator.share ? navigator.share({ title: @js($product->name), url: location.href }) : navigator.clipboard.writeText(location.href)" class="mt-3 border-b border-current text-navy">Bagikan koleksi</button></div>
                @endif
                <div class="mt-8 rounded-xl bg-white p-4 text-sm text-ink-soft ring-1 ring-line">{{ $product->stock > 0 ? 'Tersedia '.$product->stock.' item' : 'Stok habis' }}</div>
                @if ($product->stock > 0)
                    <form method="POST" action="{{ \App\Support\StorefrontContext::route('cart.store', ['product' => $product]) }}" class="mt-6 flex flex-wrap items-end gap-3">
                        @csrf
                        <label class="grid gap-2 text-sm font-semibold text-navy">Jumlah<input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-24 rounded-xl border border-line bg-white px-4 py-3"></label>
                        <x-ui.loading-button loading-label="Menambahkan...">Tambah ke keranjang</x-ui.loading-button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="border-t border-line bg-white"><div class="mx-auto max-w-7xl px-5 py-16 sm:px-8"><x-ui.section-header :eyebrow="$demoLayout === 'editorial' ? 'Complete the room' : 'Produk terkait'" :title="$demoLayout === 'editorial' ? 'Continue the story' : 'Mungkin kamu juga suka'" /><div class="mt-10 grid gap-6 md:grid-cols-2 {{ $demoLayout === 'editorial' ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">@foreach ($relatedProducts as $relatedProduct)<x-storefront.product-card :product="$relatedProduct" />@endforeach</div></div></section>
    @endif
</x-layouts::storefront>
