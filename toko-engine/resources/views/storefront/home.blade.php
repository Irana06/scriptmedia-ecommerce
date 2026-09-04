@php($demoStore = \App\Support\StorefrontContext::store())

<x-layouts::storefront title="Home">
    @if ($demoStore)
        @php($bestSellerImage = $bestSeller?->getFirstMediaUrl('product-images', 'thumb'))
        @if (($demoStore['layout'] ?? null) === 'simple')
            <section class="relative overflow-hidden bg-[#fffaf1] py-16 sm:py-24">
                <div class="absolute -top-24 right-[8%] size-72 rounded-full bg-tosca/10 blur-3xl"></div>
                <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-5 lg:grid-cols-[1fr_.75fr] sm:px-8">
                    <div><span class="inline-flex rounded-full bg-tosca-tint px-4 py-2 text-xs font-semibold tracking-wide text-tosca uppercase">Kopi enak, tanpa ribet</span><h1 class="mt-6 max-w-3xl text-4xl leading-[1.08] text-navy sm:text-6xl">{{ $demoStore['headline'] }}</h1><p class="mt-5 max-w-xl text-base leading-7 text-ink-soft sm:text-lg">{{ $demoStore['description'] }}</p><div class="mt-8"><x-ui.button :href="\App\Support\StorefrontContext::route('products.index')" variant="navy">Lihat menu</x-ui.button></div><div class="mt-9 flex flex-wrap gap-x-6 gap-y-2 text-sm text-ink-soft"><span>✓ Pesan tanpa akun</span><span>✓ Bayar via QRIS</span><span>✓ Link status order</span></div></div>
                    @if ($bestSeller)<article class="rounded-[2rem] border border-tosca/20 bg-white p-5 shadow-card"><div class="flex aspect-square items-center justify-center rounded-[1.5rem] bg-tosca-tint text-8xl text-navy/20">{{ \Illuminate\Support\Str::substr($bestSeller->name, 0, 1) }}</div><div class="p-3 pt-5"><p class="text-xs font-semibold tracking-wide text-tosca uppercase">Menu paling disukai</p><h2 class="mt-2 text-2xl text-navy">{{ $bestSeller->name }}</h2><div class="mt-4 flex items-center justify-between"><strong class="text-lg text-navy">Rp{{ number_format((float) $bestSeller->price, 0, ',', '.') }}</strong><form method="POST" action="{{ \App\Support\StorefrontContext::route('cart.store', ['product' => $bestSeller]) }}">@csrf<input type="hidden" name="quantity" value="1"><x-ui.button type="submit" variant="navy" class="px-4 py-2.5">Pesan</x-ui.button></form></div></div></article>@endif
                </div>
            </section>
        @elseif (($demoStore['layout'] ?? null) === 'business')
            <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy py-20 text-white sm:py-28">
                <div class="absolute inset-0 bg-cover bg-center opacity-70" style="background-image: url('{{ asset($demoStore['hero_banner']) }}')"></div><div class="absolute inset-0 bg-linear-to-r from-navy via-navy/90 to-navy/55"></div>
                <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-5 lg:grid-cols-[1.1fr_.9fr] sm:px-8"><div><span class="inline-flex rounded-full bg-orange px-4 py-2 text-xs font-semibold tracking-wide text-navy uppercase">Upgrade workspace-mu</span><h1 class="mt-6 max-w-3xl text-4xl leading-[1.08] text-white sm:text-6xl">{{ $demoStore['headline'] }}</h1><p class="mt-6 max-w-xl text-base leading-7 text-white/75 sm:text-lg">{{ $demoStore['description'] }}</p><div class="mt-8 flex flex-wrap gap-3"><x-ui.button :href="\App\Support\StorefrontContext::route('products.index')">Cari perangkat</x-ui.button><a href="#fitur-paket" class="inline-flex cursor-pointer items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Layanan toko</a></div><div class="mt-9 grid max-w-xl grid-cols-3 gap-3 text-center text-xs"><div class="rounded-xl border border-white/15 bg-white/10 p-3">Garansi produk</div><div class="rounded-xl border border-white/15 bg-white/10 p-3">Pencarian katalog</div><div class="rounded-xl border border-white/15 bg-white/10 p-3">QRIS + transfer</div></div></div>
                    @if ($bestSeller)<div class="relative mx-auto w-full max-w-xl overflow-hidden rounded-[1.75rem] border border-white/15 bg-white/10 p-4 shadow-2xl backdrop-blur-sm">@if ($bestSellerImage)<img src="{{ $bestSellerImage }}" alt="{{ $bestSeller->name }}" class="aspect-[4/3] w-full rounded-2xl object-cover">@else<div class="flex aspect-[4/3] items-center justify-center rounded-2xl bg-linear-to-br from-tosca-tint to-orange/20 text-9xl text-navy/25">{{ \Illuminate\Support\Str::substr($bestSeller->name, 0, 1) }}</div>@endif<div class="absolute inset-x-4 bottom-4 h-2/3 rounded-b-2xl bg-linear-to-t from-navy via-navy/35 to-transparent"></div><div class="absolute right-8 bottom-8 left-8 flex items-end justify-between gap-4"><div><span class="inline-flex rounded-full bg-orange px-3 py-1.5 text-[10px] font-semibold tracking-wide text-navy uppercase">Best seller</span><p class="mt-3 text-lg font-semibold text-white">{{ $bestSeller->name }}</p><p class="mt-1 text-sm text-white/70">Rp{{ number_format((float) $bestSeller->price, 0, ',', '.') }}</p></div><form method="POST" action="{{ \App\Support\StorefrontContext::route('cart.store', ['product' => $bestSeller]) }}">@csrf<input type="hidden" name="quantity" value="1"><button type="submit" aria-label="Tambahkan {{ $bestSeller->name }} ke keranjang" class="flex size-12 items-center justify-center rounded-full bg-orange text-2xl text-navy shadow-lg transition hover:-translate-y-1">+</button></form></div></div>@endif
                </div>
            </section>
        @else
            <section class="relative min-h-[42rem] overflow-hidden bg-[#211a17] text-white">
                <div class="absolute inset-0 bg-cover bg-center opacity-60" style="background-image: url('{{ asset($demoStore['hero_banner']) }}')"></div><div class="absolute inset-0 bg-linear-to-r from-[#211a17] via-[#211a17]/80 to-transparent"></div>
                <div class="relative mx-auto flex min-h-[42rem] max-w-7xl items-center px-5 py-20 sm:px-8"><div class="max-w-2xl"><p class="text-xs tracking-[0.35em] text-[#e9c6b7] uppercase">Nara Atelier · New collection</p><h1 class="mt-7 text-5xl leading-[1.02] font-normal text-white sm:text-7xl">{{ $demoStore['headline'] }}</h1><p class="mt-7 max-w-xl text-lg leading-8 text-white/70">{{ $demoStore['description'] }}</p><div class="mt-10 flex flex-wrap gap-4"><a href="{{ \App\Support\StorefrontContext::route('products.index') }}" class="inline-flex items-center justify-center rounded-none bg-[#f3e8df] px-7 py-3.5 text-sm font-semibold text-[#211a17] transition hover:bg-white">Explore collection</a>@if($bestSeller)<a href="{{ \App\Support\StorefrontContext::route('products.show', ['product' => $bestSeller]) }}" class="inline-flex items-center border-b border-white/50 px-1 py-3 text-sm text-white transition hover:border-white">View signature piece →</a>@endif</div><div class="mt-14 flex flex-wrap gap-7 text-xs tracking-wide text-white/55 uppercase"><span>Curated collection</span><span>Real-time shipping</span><span>Premium support</span></div></div></div>
            </section>
        @endif
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
        @if (($demoStore['layout'] ?? null) === 'simple')
            <x-ui.section-header eyebrow="Menu pilihan" title="Dibuat untuk menemani harimu" description="Tidak banyak pilihan yang membingungkan—cukup menu favorit yang siap dipesan." />
        @elseif (($demoStore['layout'] ?? null) === 'business')
            <x-ui.section-header eyebrow="Pilihan produktif" title="Perangkat yang tepat untuk kerja lebih cepat" description="Cari, bandingkan, dan temukan aksesori sesuai kebutuhan workspace-mu." />
        @elseif (($demoStore['layout'] ?? null) === 'editorial')
            <x-ui.section-header eyebrow="Curated selection" title="Pieces with presence and purpose" description="Material, proporsi, dan detail yang dipilih untuk ruang yang lebih personal." />
        @else
            <x-ui.section-header eyebrow="Produk unggulan" title="Pilihan sederhana, kualitas istimewa" description="Produk pilihan yang siap menemani keseharianmu." />
        @endif
        @if ($featuredProducts->isEmpty())
            <x-ui.empty-state class="mt-10" title="Koleksi pilihan sedang disiapkan" description="Produk unggulan terbaru akan segera hadir. Sementara itu, jelajahi seluruh katalog toko." action-label="Lihat semua produk" :action-href="\App\Support\StorefrontContext::route('products.index')" />
        @else
            <div class="mt-10 grid gap-6 md:grid-cols-2 {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'lg:grid-cols-2' : 'lg:grid-cols-3' }}">@foreach ($featuredProducts as $product)<x-storefront.product-card :product="$product" />@endforeach</div>
        @endif
    </section>

    @if ($demoStore)
        <section id="fitur-paket" class="border-y border-line {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'bg-[#211a17] text-white' : 'bg-white' }}"><div class="mx-auto grid max-w-7xl gap-8 px-5 py-16 lg:grid-cols-[.85fr_1.15fr] lg:items-center sm:px-8"><div><span class="inline-flex rounded-full px-4 py-2 text-xs font-semibold uppercase" style="color: {{ $demoStore['accent'] }}; background: {{ $demoStore['accent_soft'] }}">Paket {{ $demoStore['plan'] }}</span><h2 class="mt-5 text-3xl {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'text-white' : '' }}">{{ ($demoStore['layout'] ?? null) === 'simple' ? 'Semua kebutuhan dasar untuk mulai jualan' : (($demoStore['layout'] ?? null) === 'business' ? 'Operasional toko yang lebih siap berkembang' : 'Pengalaman premium tanpa batas katalog') }}</h2><p class="mt-4 leading-7 {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'text-white/65' : 'text-ink-soft' }}">Katalog, keranjang, checkout, pembayaran, dan tracking memakai alur Toko Engine yang sama dengan toko aktif; kemampuan tambahannya mengikuti paket.</p></div><div class="grid gap-4 sm:grid-cols-2">@foreach ([['Kapasitas katalog', $demoStore['capacity']], ['Pembayaran', $demoStore['payment']], ['Alamat toko', $demoStore['domain']], ['Pencarian & urutkan', $demoStore['catalog_sort'] ? 'Pencarian + pengurutan lengkap' : ($demoStore['catalog_search'] ? 'Pencarian produk' : 'Katalog sederhana')], ['Pengiriman', $demoStore['shipping']], ['Dukungan', $demoStore['support']], ['Perubahan konten', $demoStore['content_quota'].'× per bulan'], ['Biaya bulanan', 'Rp'.number_format($demoStore['price'], 0, ',', '.')]] as [$label, $value])<div class="rounded-card border {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'border-white/15 bg-white/5' : 'border-line bg-offwhite' }} p-5"><p class="text-sm {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'text-white/50' : 'text-ink-soft' }}">{{ $label }}</p><p class="mt-2 font-semibold">{{ $value }}</p></div>@endforeach</div></div></section>
    @endif
</x-layouts::storefront>
