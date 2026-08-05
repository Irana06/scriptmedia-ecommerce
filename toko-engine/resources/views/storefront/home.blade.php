<x-layouts::storefront title="Home">
    <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy py-20 text-white sm:py-28">
        <div class="absolute -top-28 left-[8%] size-80 rounded-full bg-orange/20 blur-3xl"></div><div class="absolute -right-24 -bottom-28 size-96 rounded-full bg-tosca/20 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-5 sm:px-8">
            <x-ui.badge variant="orange">Koleksi pilihan minggu ini</x-ui.badge>
            <h1 class="mt-6 max-w-4xl text-4xl leading-[1.08] text-white sm:text-6xl">Temukan produk lokal yang dibuat dengan cerita.</h1>
            <p class="mt-6 max-w-xl text-base leading-7 text-white/75 sm:text-lg">Belanja kebutuhan rumah dan gaya hidup dari perajin pilihan, dikemas aman dan dikirim langsung ke pintumu.</p>
            <div class="mt-8"><x-ui.button :href="route('products.index')">Belanja semua produk</x-ui.button></div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-24">
        <x-ui.section-header eyebrow="Produk unggulan" title="Pilihan sederhana, kualitas istimewa" description="Produk pilihan yang siap menemani keseharianmu." />
        @if ($featuredProducts->isEmpty())
            <x-ui.card class="mt-10 text-center"><p class="text-ink-soft">Belum ada produk unggulan. Jalankan seeder atau tambahkan produk dari admin.</p></x-ui.card>
        @else
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach ($featuredProducts as $product)<x-storefront.product-card :product="$product" />@endforeach</div>
        @endif
    </section>
</x-layouts::storefront>
