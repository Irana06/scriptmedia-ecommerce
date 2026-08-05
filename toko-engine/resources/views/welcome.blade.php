<x-layouts::storefront title="Toko Senja">
    <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy py-20 text-white sm:py-28">
        <div class="absolute -top-28 left-[8%] size-80 rounded-full bg-orange/20 blur-3xl"></div>
        <div class="absolute -right-24 -bottom-28 size-96 rounded-full bg-tosca/20 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <x-ui.badge variant="orange">Koleksi pilihan minggu ini</x-ui.badge>
                <h1 class="mt-6 max-w-3xl text-4xl leading-[1.08] text-white sm:text-6xl">
                    Temukan produk lokal yang dibuat dengan cerita.
                </h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-white/75 sm:text-lg">
                    Belanja kebutuhan rumah dan gaya hidup dari perajin pilihan, dikemas aman dan dikirim langsung ke pintumu.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-ui.button href="#koleksi">Belanja koleksi</x-ui.button>
                    <x-ui.button href="#keunggulan" variant="navy" class="ring-1 ring-white/30 hover:ring-white/50">
                        Kenapa Toko Senja?
                    </x-ui.button>
                </div>
            </div>

            <x-ui.card class="relative rotate-2 border-white/20 bg-white/95 p-4 shadow-2xl shadow-navy/30" :padding="false">
                <div class="flex aspect-[4/3] items-center justify-center rounded-[14px] bg-linear-to-br from-tosca-tint via-offwhite to-orange/25">
                    <div class="text-center">
                        <span class="mx-auto flex size-24 items-center justify-center rounded-full bg-white text-4xl shadow-lg">☕</span>
                        <p class="mt-5 text-sm tracking-[0.18em] text-tosca uppercase">Pilihan perajin</p>
                        <p class="mt-2 text-2xl font-semibold text-navy">Ruang hangat di rumah</p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </section>

    <section id="koleksi" class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-24">
        <x-ui.section-header
            eyebrow="Produk unggulan"
            title="Pilihan sederhana, kualitas istimewa"
            description="Contoh kartu produk menggunakan komponen dan token visual yang sama dengan area admin toko."
        />

        <div class="mt-10 grid gap-6 md:grid-cols-3">
            <x-ui.card :padding="false" class="group overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
                <div class="flex aspect-[4/3] items-center justify-center bg-tosca-tint text-6xl">🫖</div>
                <div class="p-6">
                    <div class="flex items-center justify-between gap-3">
                        <x-ui.badge>Rumah</x-ui.badge>
                        <span class="text-xs text-ink-soft">Stok tersedia</span>
                    </div>
                    <h2 class="mt-4 text-xl text-navy">Teko Tanah Sore</h2>
                    <p class="mt-2 text-sm leading-6 text-ink-soft">Dibentuk tangan untuk teman minum teh yang lebih tenang.</p>
                    <div class="mt-6 flex items-center justify-between">
                        <span class="font-semibold text-navy">Rp189.000</span>
                        <x-ui.button href="#" variant="navy" class="px-4 py-2.5">Lihat</x-ui.button>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card :padding="false" class="group overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
                <div class="flex aspect-[4/3] items-center justify-center bg-orange/15 text-6xl">👜</div>
                <div class="p-6">
                    <div class="flex items-center justify-between gap-3">
                        <x-ui.badge variant="orange">Terlaris</x-ui.badge>
                        <span class="text-xs text-ink-soft">Tersisa 8</span>
                    </div>
                    <h2 class="mt-4 text-xl text-navy">Tas Kanvas Rimba</h2>
                    <p class="mt-2 text-sm leading-6 text-ink-soft">Kanvas tebal dengan ruang lega untuk aktivitas sehari-hari.</p>
                    <div class="mt-6 flex items-center justify-between">
                        <span class="font-semibold text-navy">Rp245.000</span>
                        <x-ui.button href="#" variant="navy" class="px-4 py-2.5">Lihat</x-ui.button>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card :padding="false" class="group overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
                <div class="flex aspect-[4/3] items-center justify-center bg-navy/8 text-6xl">🕯️</div>
                <div class="p-6">
                    <div class="flex items-center justify-between gap-3">
                        <x-ui.badge variant="navy">Baru</x-ui.badge>
                        <span class="text-xs text-ink-soft">Stok tersedia</span>
                    </div>
                    <h2 class="mt-4 text-xl text-navy">Lilin Aroma Hujan</h2>
                    <p class="mt-2 text-sm leading-6 text-ink-soft">Aroma kayu dan tanah basah untuk sore yang lebih nyaman.</p>
                    <div class="mt-6 flex items-center justify-between">
                        <span class="font-semibold text-navy">Rp129.000</span>
                        <x-ui.button href="#" variant="navy" class="px-4 py-2.5">Lihat</x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </section>

    <section id="keunggulan" class="border-y border-line bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-14 sm:px-8 md:grid-cols-3">
            <div><x-ui.badge>01</x-ui.badge><h2 class="mt-4 text-xl text-navy">Kurasi berkualitas</h2><p class="mt-2 text-sm leading-6 text-ink-soft">Setiap produk dipilih dari perajin dan brand lokal tepercaya.</p></div>
            <div><x-ui.badge variant="orange">02</x-ui.badge><h2 class="mt-4 text-xl text-navy">Pembayaran aman</h2><p class="mt-2 text-sm leading-6 text-ink-soft">Proses checkout ringkas dengan informasi transaksi yang jelas.</p></div>
            <div><x-ui.badge variant="navy">03</x-ui.badge><h2 class="mt-4 text-xl text-navy">Dukungan responsif</h2><p class="mt-2 text-sm leading-6 text-ink-soft">Kami siap membantu sebelum dan sesudah pesanan dikirim.</p></div>
        </div>
    </section>
</x-layouts::storefront>
