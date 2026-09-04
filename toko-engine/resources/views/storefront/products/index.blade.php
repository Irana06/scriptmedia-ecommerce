@php($demoStore = \App\Support\StorefrontContext::store())

<x-layouts::storefront title="Produk">
    <section class="border-b border-line {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'bg-[#211a17] text-white' : 'bg-white' }} py-14 sm:py-18">
        <div class="mx-auto max-w-7xl px-5 sm:px-8"><x-ui.badge variant="orange">{{ ($demoStore['layout'] ?? null) === 'editorial' ? 'The collection' : 'Katalog' }}</x-ui.badge><h1 class="mt-4 text-4xl {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'text-white' : 'text-navy' }} sm:text-5xl">{{ ($demoStore['layout'] ?? null) === 'simple' ? 'Menu favorit hari ini' : (($demoStore['layout'] ?? null) === 'editorial' ? 'Objects for considered living' : 'Semua produk') }}</h1><p class="mt-3 max-w-2xl {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'text-white/65' : 'text-ink-soft' }}">{{ ($demoStore['layout'] ?? null) === 'business' ? 'Cari perangkat berdasarkan kebutuhan dan kategori ruang kerjamu.' : (($demoStore['layout'] ?? null) === 'editorial' ? 'Jelajahi koleksi interior terkurasi dan temukan karya yang melengkapi ruangmu.' : 'Pilihan kopi dan camilan yang disiapkan dengan sederhana.') }}</p></div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
        @if (\App\Support\StorefrontContext::allows('catalog_search'))
            <form method="GET" action="{{ \App\Support\StorefrontContext::route('products.index') }}" class="mb-8 grid gap-3 rounded-card border border-line bg-white p-4 shadow-card sm:grid-cols-[1fr_auto_auto]">
                @if ($categorySlug !== '')<input type="hidden" name="category" value="{{ $categorySlug }}">@endif
                <label class="sr-only" for="catalog-search">Cari produk</label>
                <input id="catalog-search" type="search" name="q" value="{{ $search }}" placeholder="{{ ($demoStore['layout'] ?? null) === 'editorial' ? 'Cari koleksi...' : 'Cari nama produk...' }}" class="min-w-0 rounded-xl border border-line px-4 py-3 text-navy outline-none transition focus:border-tosca focus:ring-2 focus:ring-tosca/15">
                @if (\App\Support\StorefrontContext::allows('catalog_sort'))
                    @php($sortOptions = ['latest' => 'Koleksi terbaru', 'price-low' => 'Harga terendah', 'price-high' => 'Harga tertinggi', 'name' => 'Nama A–Z'])
                    <div
                        class="relative min-w-52"
                        x-data="{ open: false, value: @js($sort), labels: @js($sortOptions) }"
                        x-on:keydown.escape.window="open = false"
                        x-on:click.outside="open = false"
                    >
                        <input type="hidden" name="sort" x-bind:value="value">
                        <button
                            type="button"
                            class="flex h-full min-h-12 w-full items-center justify-between gap-5 border border-line bg-[#fdfaf7] px-5 py-3 text-left text-sm text-navy outline-none transition hover:border-tosca/50 hover:bg-white focus-visible:border-tosca focus-visible:ring-2 focus-visible:ring-tosca/15"
                            x-on:click="open = ! open"
                            x-bind:aria-expanded="open"
                            aria-haspopup="listbox"
                        >
                            <span x-text="labels[value]"></span>
                            <svg class="size-4 shrink-0 text-tosca transition duration-200" x-bind:class="open && 'rotate-180'" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div
                            x-cloak
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="translate-y-1 opacity-0"
                            x-transition:enter-end="translate-y-0 opacity-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="translate-y-0 opacity-100"
                            x-transition:leave-end="translate-y-1 opacity-0"
                            class="absolute z-30 mt-2 w-full overflow-hidden border border-line bg-white p-1.5 shadow-[0_18px_45px_rgba(33,26,23,0.16)]"
                            role="listbox"
                        >
                            @foreach ($sortOptions as $optionValue => $optionLabel)
                                <button type="button" role="option" x-on:click="value = @js($optionValue); open = false" x-bind:aria-selected="value === @js($optionValue)" class="flex w-full items-center justify-between gap-4 px-3.5 py-2.5 text-left text-sm text-ink-soft transition hover:bg-offwhite hover:text-navy focus-visible:bg-offwhite focus-visible:text-navy focus-visible:outline-none">
                                    <span>{{ $optionLabel }}</span>
                                    <svg x-show="value === @js($optionValue)" class="size-4 text-tosca" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 10 4 4 8-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <x-ui.button type="submit" variant="navy">{{ \App\Support\StorefrontContext::allows('catalog_sort') ? 'Terapkan' : 'Cari produk' }}</x-ui.button>
            </form>
        @endif

        <div class="flex flex-wrap gap-2">
            <a href="{{ \App\Support\StorefrontContext::route('products.index') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $categorySlug === '' ? 'bg-navy text-white' : 'border border-line bg-white text-ink-soft hover:text-navy' }}">Semua</a>
            @foreach ($categories as $category)
                <a href="{{ \App\Support\StorefrontContext::route('products.index', ['category' => $category->slug]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $categorySlug === $category->slug ? 'bg-navy text-white' : 'border border-line bg-white text-ink-soft hover:text-navy' }}">{{ $category->name }}</a>
            @endforeach
        </div>

        @if ($products->isEmpty())
            <x-ui.empty-state
                class="mt-10"
                title="{{ $search !== '' ? 'Produk tidak ditemukan' : ($categorySlug === '' ? 'Katalog sedang disiapkan' : 'Belum ada produk di kategori ini') }}"
                description="{{ $search !== '' ? 'Coba kata kunci lain atau reset pencarian.' : ($categorySlug === '' ? 'Koleksi produk akan segera tersedia. Kunjungi kembali etalase ini nanti.' : 'Coba jelajahi kategori lain untuk menemukan produk yang kamu cari.') }}"
                :action-href="$search !== '' || $categorySlug !== '' ? \App\Support\StorefrontContext::route('products.index') : \App\Support\StorefrontContext::route('home')"
                :action-label="$search !== '' ? 'Reset pencarian' : ($categorySlug === '' ? 'Kembali ke beranda' : 'Lihat semua produk')"
            />
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-2 {{ ($demoStore['layout'] ?? null) === 'editorial' ? 'lg:grid-cols-2' : 'lg:grid-cols-3' }}">@foreach ($products as $product)<x-storefront.product-card :product="$product" />@endforeach</div>
            <div class="mt-10">{{ $products->links() }}</div>
        @endif
    </section>
</x-layouts::storefront>
