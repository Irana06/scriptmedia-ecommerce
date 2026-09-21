@props(['product'])

@php
    $imageUrl = $product->getFirstMediaUrl('product-images', 'thumb');
    $visual = \App\Support\StorefrontContext::productVisual($product);
    $demoLayout = \App\Support\StorefrontContext::store()['layout'] ?? 'default';
    $isProDemo = \App\Support\StorefrontContext::slug() === 'pro';
    $isWishlisted = $isProDemo && app(\App\Services\WishlistService::class)->contains($product);
    $hasVariants = $product->hasVariants();
    $stock = $product->availableStock();
    $detailUrl = \App\Support\StorefrontContext::route('products.show', ['product' => $product]);
@endphp

<x-ui.card :padding="false" {{ $attributes->class('group relative overflow-hidden transition hover:-translate-y-1 hover:shadow-xl '.$demoLayout.'-product-card') }}>
    @if ($isProDemo)
        <form method="POST" action="{{ \App\Support\StorefrontContext::route($isWishlisted ? 'wishlist.destroy' : 'wishlist.store', ['product' => $product]) }}" class="absolute top-4 right-4 z-10">@csrf @if($isWishlisted) @method('DELETE') @endif<button type="submit" class="flex size-10 items-center justify-center rounded-full border border-white/70 bg-white/90 text-lg text-navy shadow-card backdrop-blur transition hover:scale-105" aria-label="{{ $isWishlisted ? 'Hapus dari' : 'Simpan ke' }} favorit">{{ $isWishlisted ? '♥' : '♡' }}</button></form>
    @endif
    <a href="{{ $detailUrl }}" class="block">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="{{ $demoLayout === 'editorial' ? 'aspect-[5/3]' : 'aspect-[4/3]' }} w-full object-cover transition duration-500 group-hover:scale-[1.02]">
        @elseif ($visual)
            <div class="flex {{ $demoLayout === 'editorial' ? 'aspect-[5/3]' : 'aspect-[4/3]' }} items-center justify-center text-6xl transition duration-500 group-hover:scale-[1.04]" style="background-color: {{ $visual['color'] }}" role="img" aria-label="Ilustrasi {{ $product->name }}">{{ $visual['icon'] }}</div>
        @else
            <div class="flex {{ $demoLayout === 'editorial' ? 'aspect-[5/3]' : 'aspect-[4/3]' }} items-center justify-center bg-linear-to-br from-tosca-tint to-orange/15 text-6xl text-navy/25">{{ \Illuminate\Support\Str::substr($product->name, 0, 1) }}</div>
        @endif
    </a>
    <div class="{{ $demoLayout === 'simple' ? 'p-5' : 'p-6' }}">
        <div class="flex items-center justify-between gap-3"><x-ui.badge>{{ $product->category->name }}</x-ui.badge>@if($demoLayout !== 'simple')<span class="text-xs text-ink-soft">{{ $stock > 0 ? 'Stok '.$stock : 'Habis' }}</span>@endif</div>
        <a href="{{ $detailUrl }}"><h2 class="mt-4 text-xl text-navy transition group-hover:text-tosca">{{ $product->name }}</h2></a>
        @if($demoLayout !== 'simple')<p class="mt-2 line-clamp-2 text-sm leading-6 text-ink-soft">{{ $product->description }}</p>@endif
        @if ($hasVariants)
            <p class="mt-3 text-xs text-ink-soft">{{ implode(' · ', array_keys($product->optionGroups())) }} tersedia</p>
        @endif
        <div class="mt-4">
            <span class="block font-semibold text-navy">
                Rp{{ number_format($product->displayPrice(), 0, ',', '.') }}@if ($product->hasPriceRange())<span class="text-sm font-normal text-ink-soft"> – Rp{{ number_format($product->maxPrice(), 0, ',', '.') }}</span>@endif
            </span>
            @if ($stock < 1)
                <p class="mt-3 text-sm text-ink-soft">Stok habis</p>
            @elseif ($hasVariants)
                {{-- A variant has to be picked, and that belongs on the detail page. --}}
                <div class="mt-3"><x-ui.button :href="$detailUrl" variant="navy" class="w-full !px-3 !py-2.5 !text-xs whitespace-nowrap">Pilih varian</x-ui.button></div>
            @else
                @php($dialogPayload = [
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'icon' => $visual['icon'] ?? mb_substr($product->name, 0, 1),
                    'color' => $visual['color'] ?? '#e3f4f3',
                    'cartUrl' => \App\Support\StorefrontContext::route('cart.store', ['product' => $product]),
                    'buyUrl' => \App\Support\StorefrontContext::route('cart.buy', ['product' => $product]),
                ])
                {{--
                    x-data makes this an Alpine root so the handlers below are initialised,
                    and the payload rides on a data attribute because Blade does not compile
                    @js() inside a component's attribute value.
                --}}
                <div class="mt-3 grid gap-2 sm:grid-cols-2" x-data data-product="{{ json_encode($dialogPayload) }}">
                    <x-ui.button type="button" variant="outline" class="w-full cursor-pointer !px-3 !py-2.5 !text-xs whitespace-nowrap" x-on:click="$dispatch('open-quantity-dialog', { intent: 'cart', product: JSON.parse($root.dataset.product) })">+ Keranjang</x-ui.button>
                    <x-ui.button type="button" variant="navy" class="w-full cursor-pointer !px-3 !py-2.5 !text-xs whitespace-nowrap" x-on:click="$dispatch('open-quantity-dialog', { intent: 'buy', product: JSON.parse($root.dataset.product) })">Beli sekarang</x-ui.button>
                </div>
            @endif
        </div>
    </div>
</x-ui.card>
