@php
    $demoStore = \App\Support\StorefrontContext::store();
@endphp

<x-layouts::storefront title="Home">
    @if ($demoStore)
        @php
            $bestSellerImage = $bestSeller?->getFirstMediaUrl('product-images', 'thumb');
            $tierLevel = $demoStore['tier_level'] ?? 1;
            $journey = match ($demoStore['layout'] ?? null) {
                'simple' => [
                    'eyebrow' => 'Paket teman sore',
                    'title' => 'Satu paket, satu jeda yang lebih hangat.',
                    'description' => 'Pilih kopi, camilan, atau hampers sederhana. Pesan tanpa membuat akun dan simpan link WhatsApp untuk melihat statusnya kapan saja.',
                    'steps' => [['01', 'Pilih menu'], ['02', 'Bayar QRIS'], ['03', 'Pantau order']],
                    'image_alt' => 'Pilihan kopi dan camilan Kedai Rona',
                ],
                'business' => [
                    'eyebrow' => 'Belanja praktis',
                    'title' => 'Perangkat pilihan, proses pesanan tetap sederhana.',
                    'description' => 'Semua kemudahan Starter tetap tersedia. Pilih produk tanpa membuat akun, bayar melalui QRIS atau transfer bank, lalu pantau status dari link order.',
                    'steps' => [['01', 'Pilih produk'], ['02', 'QRIS / transfer'], ['03', 'Pantau order']],
                    'image_alt' => 'Pilihan perangkat kerja Shicomp Store',
                ],
                default => [
                    'eyebrow' => 'Seamless purchase',
                    'title' => 'A considered journey, from discovery to delivery.',
                    'description' => 'Pengalaman dasar Starter tetap hadir dalam desain premium: belanja tanpa akun, pilihan pembayaran lebih lengkap, dan link privat untuk memantau pesanan.',
                    'steps' => [['01', 'Explore'], ['02', 'Multi-payment'], ['03', 'Track order']],
                    'image_alt' => 'Koleksi interior pilihan Nara Atelier',
                ],
            };
            $journeyImage = $demoStore['story_image'] ?? $demoStore['hero_banner'] ?? null;
        @endphp
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

    @if ($demoStore)
        <section class="border-y border-line bg-white">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-5 py-16 lg:grid-cols-2 sm:px-8">
                @if ($journeyImage)
                    <img src="{{ asset($journeyImage) }}" alt="{{ $journey['image_alt'] }}" class="aspect-[3/2] w-full rounded-[2rem] object-cover shadow-card">
                @endif
                <div>
                    <p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">{{ $journey['eyebrow'] }}</p>
                    <h2 class="mt-4 text-3xl sm:text-4xl">{{ $journey['title'] }}</h2>
                    <p class="mt-4 max-w-xl leading-7 text-ink-soft">{{ $journey['description'] }}</p>
                    <div class="mt-7 grid gap-4 sm:grid-cols-3">
                        @foreach ($journey['steps'] as [$number, $label])
                            <div class="rounded-xl bg-offwhite p-4">
                                <span class="text-xs text-tosca">{{ $number }}</span>
                                <p class="mt-2 font-semibold">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($demoStore && $tierLevel >= 2)
        @php
            $standardHighlights = [
                ['Pencarian produk', 'Temukan produk dengan kata kunci tanpa menelusuri seluruh katalog.'],
                ['Filter harga & stok', 'Saring katalog berdasarkan anggaran dan ketersediaan barang.'],
                ['Produk terkait', 'Tampilkan rekomendasi relevan dari kategori yang sedang dilihat.'],
                ['Custom domain', 'Gunakan alamat domain bisnis sendiri untuk identitas yang lebih kuat.'],
            ];
        @endphp
        <section class="border-y border-line bg-white">
            <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">Termasuk kemampuan Standard</p>
                        <h2 class="mt-3 text-3xl">Katalog yang lebih mudah dijelajahi</h2>
                        @if ($tierLevel >= 3)
                            <p class="mt-3 text-sm text-ink-soft">Seluruh fitur Starter dan Standard tetap tersedia di paket Pro.</p>
                        @else
                            <p class="mt-3 text-sm text-ink-soft">Seluruh fitur dasar Starter tetap tersedia, ditambah perangkat katalog Standard.</p>
                        @endif
                    </div>
                    <a href="{{ \App\Support\StorefrontContext::route('products.index') }}" class="text-sm font-semibold text-navy">Cari seluruh katalog →</a>
                </div>
                <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($standardHighlights as [$title, $copy])
                        <article class="rounded-card border border-line bg-offwhite p-6 transition hover:-translate-y-1 hover:border-orange">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-orange text-navy">✓</div>
                            <h3 class="mt-5 text-xl">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-ink-soft">{{ $copy }}</p>
                        </article>
                    @endforeach
                </div>
                <div class="mt-8 grid gap-4 rounded-card bg-navy p-6 text-white sm:grid-cols-3">
                    <div><strong class="text-2xl text-orange">{{ $tierLevel >= 3 ? '6 jam' : '12 jam' }}</strong><p class="mt-1 text-sm text-white/65">Respons dukungan prioritas</p></div>
                    <div><strong class="text-2xl text-orange">{{ $tierLevel >= 3 ? 'Unlimited' : '150' }}</strong><p class="mt-1 text-sm text-white/65">Kapasitas produk</p></div>
                    <div><strong class="text-2xl text-orange">{{ $demoStore['content_quota'] }}×</strong><p class="mt-1 text-sm text-white/65">Bantuan perubahan konten</p></div>
                </div>
            </div>
        </section>
    @endif

    @if ($demoStore && $tierLevel >= 3)
        <section class="border-y border-line bg-white">
            <div class="mx-auto max-w-7xl px-5 py-20 sm:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs tracking-[0.3em] text-tosca uppercase">The Nara experience</p>
                    <h2 class="mt-5 text-4xl sm:text-5xl">More than objects. A considered service.</h2>
                </div>
                <div class="mt-12 grid gap-px overflow-hidden border border-line bg-line md:grid-cols-3">
                    @foreach ([['01', 'Private curation', 'Konsultasi bulanan untuk menyusun pilihan yang menyatu dengan ruangmu.'], ['02', 'Connected delivery', 'Kesiapan integrasi tarif dan layanan kurir secara real-time.'], ['03', 'Aftercare', 'Dukungan prioritas hingga akhir pekan untuk pengalaman yang tenang.']] as [$number, $title, $copy])
                        <article class="bg-white p-8">
                            <span class="text-xs tracking-[0.2em] text-tosca">{{ $number }}</span>
                            <h3 class="mt-8 text-2xl">{{ $title }}</h3>
                            <p class="mt-4 text-sm leading-7 text-ink-soft">{{ $copy }}</p>
                        </article>
                    @endforeach
                </div>
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
            <div class="mt-10 grid gap-6 md:grid-cols-2 {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'lg:grid-cols-2' : 'lg:grid-cols-3' }}">
                @foreach ($featuredProducts as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    @if ($demoStore)
        @php
            $faqItems = [
                ['Apakah harus membuat akun?', 'Tidak. Pilih produk, checkout, lalu simpan link status order yang dikirim melalui WhatsApp.'],
                ['Apakah pesanan bisa dipantau?', 'Bisa. Setiap order memiliki link pribadi untuk melihat pembayaran dan proses pesanan.'],
            ];

            if ($tierLevel >= 2) {
                $faqItems[] = ['Apakah produk bisa dicari dan difilter?', 'Bisa. Fitur Standard dan Pro menyediakan pencarian, kategori, filter harga, serta filter stok.'];
                $faqItems[] = ['Apakah bisa memakai domain sendiri?', 'Bisa. Dukungan custom domain tersedia mulai paket Standard.'];
            }

            if ($tierLevel >= 3) {
                $faqItems[] = ['Apakah saya bisa menyimpan koleksi?', 'Bisa. Paket Pro memiliki wishlist untuk menyimpan produk pilihan selama sesi belanja.'];
                $faqItems[] = ['Apakah tersedia ongkir real-time?', 'Toko Pro siap dihubungkan ke layanan kurir untuk menampilkan estimasi ongkir real-time.'];
            }

            $faqItems[] = ['Bagaimana cara membayar?', 'Metode yang tersedia pada paket ini: '.$demoStore['payment'].'.'];
        @endphp
        <section class="bg-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-5 py-20 lg:grid-cols-[.7fr_1.3fr] sm:px-8">
                <div>
                    <p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">Bantuan cepat</p>
                    <h2 class="mt-4 text-3xl">Hal yang sering ditanyakan</h2>
                    <p class="mt-4 leading-7 text-ink-soft">Informasi penting sebelum menyelesaikan pesanan.</p>
                </div>
                <div class="divide-y divide-line border-y border-line">
                    @foreach ($faqItems as [$question, $answer])
                        <details class="group py-5">
                            <summary class="flex list-none items-center justify-between gap-4 font-semibold text-navy">
                                <span>{{ $question }}</span>
                                <span class="text-xl text-tosca transition group-open:rotate-45">+</span>
                            </summary>
                            <p class="mt-3 max-w-2xl pr-10 text-sm leading-7 text-ink-soft">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($demoStore)
        <section id="fitur-paket" class="border-y border-line {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'bg-[#211a17] text-white' : 'bg-white' }}">
            <div class="mx-auto grid max-w-7xl gap-8 px-5 py-16 lg:grid-cols-[.85fr_1.15fr] lg:items-center sm:px-8">
                <div>
                    <span class="inline-flex rounded-full px-4 py-2 text-xs font-semibold uppercase" style="color: {{ $demoStore['accent'] }}; background: {{ $demoStore['accent_soft'] }}">Paket {{ $demoStore['plan'] }}</span>
                    <h2 class="mt-5 text-3xl {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'text-white' : '' }}">{{ ($demoStore['layout'] ?? null) === 'simple' ? 'Semua kebutuhan dasar untuk mulai jualan' : (($demoStore['layout'] ?? null) === 'business' ? 'Operasional toko yang lebih siap berkembang' : 'Pengalaman premium tanpa batas katalog') }}</h2>
                    <p class="mt-4 leading-7 {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'text-white/65' : 'text-ink-soft' }}">Katalog, keranjang, checkout, pembayaran, dan tracking memakai alur Toko Engine yang sama dengan toko aktif; kemampuan tambahannya mengikuti paket.</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($demoStore['includes'] as $includedPlan)
                            <span class="rounded-full border px-3 py-1.5 text-xs {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'border-white/20 text-white/75' : 'border-line text-ink-soft' }}">✓ Fitur {{ $includedPlan }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([['Kapasitas katalog', $demoStore['capacity']], ['Pembayaran', $demoStore['payment']], ['Alamat toko', $demoStore['domain']], ['Pencarian & urutkan', $demoStore['catalog_sort'] ? 'Pencarian + pengurutan lengkap' : ($demoStore['catalog_search'] ? 'Pencarian produk' : 'Katalog sederhana')], ['Pengiriman', $demoStore['shipping']], ['Dukungan', $demoStore['support']], ['Perubahan konten', $demoStore['content_quota'].'× per bulan'], ['Biaya bulanan', 'Rp'.number_format($demoStore['price'], 0, ',', '.')]] as [$label, $value])
                        <div class="rounded-card border {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'border-white/15 bg-white/5' : 'border-line bg-offwhite' }} p-5">
                            <p class="text-sm {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'text-white/50' : 'text-ink-soft' }}">{{ $label }}</p>
                            <p class="mt-2 font-semibold">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts::storefront>
