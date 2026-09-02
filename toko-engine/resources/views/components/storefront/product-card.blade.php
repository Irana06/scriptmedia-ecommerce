@props(['product'])

@php($imageUrl = $product->getFirstMediaUrl('product-images', 'thumb'))

<x-ui.card :padding="false" {{ $attributes->class('group overflow-hidden transition hover:-translate-y-1 hover:shadow-xl') }}>
    <a href="{{ \App\Support\StorefrontContext::route('products.show', ['product' => $product]) }}" class="block">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover">
        @else
            <div class="flex aspect-[4/3] items-center justify-center bg-linear-to-br from-tosca-tint to-orange/15 text-6xl text-navy/25">{{ \Illuminate\Support\Str::substr($product->name, 0, 1) }}</div>
        @endif
    </a>
    <div class="p-6">
        <div class="flex items-center justify-between gap-3"><x-ui.badge>{{ $product->category->name }}</x-ui.badge><span class="text-xs text-ink-soft">{{ $product->stock > 0 ? 'Stok '.$product->stock : 'Habis' }}</span></div>
        <a href="{{ \App\Support\StorefrontContext::route('products.show', ['product' => $product]) }}"><h2 class="mt-4 text-xl text-navy transition group-hover:text-tosca">{{ $product->name }}</h2></a>
        <p class="mt-2 line-clamp-2 text-sm leading-6 text-ink-soft">{{ $product->description }}</p>
        <div class="mt-6 flex items-center justify-between gap-3">
            <span class="font-semibold text-navy">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</span>
            @if ($product->stock > 0)
                <form method="POST" action="{{ \App\Support\StorefrontContext::route('cart.store', ['product' => $product]) }}">@csrf<input type="hidden" name="quantity" value="1"><x-ui.button type="submit" variant="navy" class="px-4 py-2.5 cursor-pointer">+ Cart</x-ui.button></form>
            @endif
        </div>
    </div>
</x-ui.card>
