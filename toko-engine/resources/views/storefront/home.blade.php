@php($demoStore = \App\Support\StorefrontContext::store())

<x-layouts::storefront title="Home">
    @if ($demoStore)
        <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy py-20 text-white sm:py-28">
            @if (isset($demoStore['hero_banner']))
                <div class="absolute inset-0 bg-cover bg-center opacity-35" style="background-image: url('{{ asset($demoStore['hero_banner']) }}')"></div>
                <div class="absolute inset-0 bg-linear-to-r from-navy via-navy/85 to-navy/55"></div>
            @endif
            <div class="absolute -top-28 left-[8%] size-80 rounded-full opacity-20 blur-3xl" style="background: {{ $demoStore['accent'] }}"></div>
            <div class="absolute -right-20 -bottom-28 size-96 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-5 lg:grid-cols-[1.1fr_.9fr] sm:px-8">
                <div>
                    <span class="inline-flex rounded-full px-4 py-2 text-xs font-semibold tracking-wide uppercase" style="color: {{ $demoStore['accent'] }}; background: {{ $demoStore['accent_soft'] }}">Koleksi pilihan minggu ini</span>
                    <h1 class="mt-6 max-w-3xl text-4xl leading-[1.08] text-white sm:text-6xl">{{ $demoStore['headline'] }}</h1>
                    <p class="mt-6 max-w-xl text-base leading-7 text-white/75 sm:text-lg">{{ $demoStore['description'] }}</p>
                    <div class="mt-8 flex flex-wrap gap-3"><x-ui.button :href="\App\Support\StorefrontContext::route('products.index')">Belanja sekarang</x-ui.button><a href="#fitur-paket" class="inline-flex cursor-pointer items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-white/10">Lihat fitur paket</a></div>
                </div>
                @if ($bestSeller)
                    @php($bestSellerImage = $bestSeller->getFirstMediaUrl('product-images', 'thumb'))
                    <div class="relative mx-auto w-full max-w-xl overflow-hidden rounded-[1.75rem] border border-white/15 bg-white/10 p-4 shadow-2xl backdrop-blur-sm">
                        @if ($bestSellerImage)
                            <img src="{{ $bestSellerImage }}" alt="{{ $bestSeller->name }}" class="aspect-[4/3] w-full rounded-2xl object-cover">
                        @else
                            <div class="flex aspect-[4/3] items-center justify-center rounded-2xl bg-linear-to-br from-tosca-tint to-orange/20 text-9xl text-navy/25">{{ \Illuminate\Support\Str::substr($bestSeller->name, 0, 1) }}</div>
                        @endif
                        <div class="absolute inset-x-4 bottom-4 h-2/3 rounded-b-2xl bg-linear-to-t from-navy via-navy/35 to-transparent"></div>
                        <div class="absolute right-8 bottom-8 left-8 flex items-end justify-between gap-4">
                            <div><span class="inline-flex rounded-full px-3 py-1.5 text-[10px] font-semibold tracking-wide text-white uppercase" style="background: {{ $demoStore['accent'] }}">Paling laku</span><p class="mt-3 text-lg font-semibold text-white">{{ $bestSeller->name }}</p><p class="mt-1 text-sm text-white/70">Rp{{ number_format((float) $bestSeller->price, 0, ',', '.') }}</p></div>
                            <form method="POST" action="{{ \App\Support\StorefrontContext::route('cart.store', ['product' => $bestSeller]) }}">@csrf<input type="hidden" name="quantity" value="1"><button type="submit" aria-label="Tambahkan {{ $bestSeller->name }} ke keranjang" class="flex size-12 shrink-0 cursor-pointer items-center justify-center rounded-full text-2xl font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-1 hover:brightness-110" style="background: {{ $demoStore['accent'] }}">+</button></form>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @else
        <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy py-20 text-white sm:py-28">
            <div class="absolute -top-28 left-[8%] size-80 rounded-full bg-orange/20 blur-3xl"></div><div class="absolute -right-24 -bottom-28 size-96 rounded-full bg-tosca/20 blur-3xl"></div>
            <div class="relative mx-auto max-w-7xl px-5 sm:px-8">
                <x-ui.badge variant="orange">Koleksi pilihan minggu ini</x-ui.badge>
                <h1 class="mt-6 max-w-4xl text-4xl leading-[1.08] text-white sm:text-6xl">Temukan produk lokal yang dibuat dengan cerita.</h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-white/75 sm:text-lg">Belanja kebutuhan rumah dan gaya hidup dari perajin pilihan, dikemas aman dan dikirim langsung ke pintumu.</p>
                <div class="mt-8"><x-ui.button :href="\App\Support\StorefrontContext::route('products.index')">Belanja semua produk</x-ui.button></div>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-24">
        <x-ui.section-header eyebrow="Produk unggulan" title="Pilihan sederhana, kualitas istimewa" description="Produk pilihan yang siap menemani keseharianmu." />
        @if ($featuredProducts->isEmpty())
            <x-ui.empty-state class="mt-10" title="Koleksi pilihan sedang disiapkan" description="Produk unggulan terbaru akan segera hadir. Sementara itu, jelajahi seluruh katalog toko." action-label="Lihat semua produk" :action-href="\App\Support\StorefrontContext::route('products.index')" />
        @else
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach ($featuredProducts as $product)<x-storefront.product-card :product="$product" />@endforeach</div>
        @endif
    </section>

    @if ($demoStore)
        <section id="fitur-paket" class="border-y border-line bg-white"><div class="mx-auto grid max-w-7xl gap-8 px-5 py-16 lg:grid-cols-[.85fr_1.15fr] lg:items-center sm:px-8"><div><span class="inline-flex rounded-full px-4 py-2 text-xs font-semibold uppercase" style="color: {{ $demoStore['accent'] }}; background: {{ $demoStore['accent_soft'] }}">Paket {{ $demoStore['plan'] }}</span><h2 class="mt-5 text-3xl">Fitur toko berjalan dari Toko Engine</h2><p class="mt-4 leading-7 text-ink-soft">Katalog, detail produk, keranjang, checkout, pembayaran, serta tracking order pada demo ini memakai alur aplikasi yang sama dengan toko aktif.</p></div><div class="grid gap-4 sm:grid-cols-2">@foreach ([['Kapasitas katalog', $demoStore['capacity']], ['Pembayaran', $demoStore['payment']], ['Alamat toko', $demoStore['domain']], ['Biaya bulanan', 'Rp'.number_format($demoStore['price'], 0, ',', '.')]] as [$label, $value])<div class="rounded-card border border-line bg-offwhite p-5"><p class="text-sm text-ink-soft">{{ $label }}</p><p class="mt-2 font-semibold">{{ $value }}</p></div>@endforeach</div></div></section>
    @endif
</x-layouts::storefront>
