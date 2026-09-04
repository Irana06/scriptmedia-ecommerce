<x-layouts::storefront title="Wishlist">
    <section class="border-b border-line bg-[#211a17] py-14 text-white sm:py-18">
        <div class="mx-auto max-w-7xl px-5 sm:px-8"><p class="text-xs tracking-[0.3em] text-[#e9c6b7] uppercase">Saved collection</p><h1 class="mt-4 text-4xl text-white sm:text-5xl">Your considered pieces</h1><p class="mt-3 max-w-2xl text-white/60">Koleksi ini tersimpan pada perangkat yang sedang kamu gunakan.</p></div>
    </section>
    <section class="mx-auto max-w-7xl px-5 py-14 sm:px-8">
        @if ($products->isEmpty())
            <x-ui.empty-state title="Wishlist masih kosong" description="Simpan karya yang menarik perhatianmu, lalu kembali saat siap memilih." action-label="Explore collection" :action-href="\App\Support\StorefrontContext::route('products.index')" />
        @else
            <div class="grid gap-6 md:grid-cols-2">@foreach($products as $product)<x-storefront.product-card :product="$product"/>@endforeach</div>
        @endif
    </section>
</x-layouts::storefront>
