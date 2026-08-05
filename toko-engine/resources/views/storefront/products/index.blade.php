<x-layouts::storefront title="Produk">
    <section class="border-b border-line bg-white py-14 sm:py-18">
        <div class="mx-auto max-w-7xl px-5 sm:px-8"><x-ui.badge variant="orange">Katalog</x-ui.badge><h1 class="mt-4 text-4xl text-navy sm:text-5xl">Semua produk</h1><p class="mt-3 max-w-2xl text-ink-soft">Temukan produk berdasarkan kategori yang paling sesuai untukmu.</p></div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $categorySlug === '' ? 'bg-navy text-white' : 'border border-line bg-white text-ink-soft hover:text-navy' }}">Semua</a>
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $categorySlug === $category->slug ? 'bg-navy text-white' : 'border border-line bg-white text-ink-soft hover:text-navy' }}">{{ $category->name }}</a>
            @endforeach
        </div>

        @if ($products->isEmpty())
            <x-ui.card class="mt-10 text-center"><p class="text-ink-soft">Belum ada produk dalam kategori ini.</p></x-ui.card>
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach ($products as $product)<x-storefront.product-card :product="$product" />@endforeach</div>
            <div class="mt-10">{{ $products->links() }}</div>
        @endif
    </section>
</x-layouts::storefront>
