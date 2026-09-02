<!DOCTYPE html>
<html lang="id">
    <head>
        @include('partials.head', ['title' => $store['store_name'].' · Demo '.$store['plan']])
        <style>
            :root { --demo-accent: {{ $store['accent'] }}; --demo-accent-soft: {{ $store['accent_soft'] }}; }
            .demo-accent { color: var(--demo-accent); }
            .demo-accent-bg { background-color: var(--demo-accent); }
            .demo-accent-soft { background-color: var(--demo-accent-soft); }
        </style>
    </head>
    @php($bestSeller = $store['products'][0])
    <body class="min-h-screen bg-offwhite text-navy">
        <div id="demo-store" data-store="{{ $slug }}">
            <div class="bg-navy px-5 py-2.5 text-center text-xs text-white sm:text-sm">
                <span class="font-semibold">Demo paket {{ $store['plan'] }}</span>
                <span class="text-white/65"> · Pilih paket lain:</span>
                @foreach ($allStores as $storeSlug => $option)
                    <a href="{{ route('demo.store', $storeSlug) }}" class="ml-2 rounded-full px-2.5 py-1 transition hover:bg-white/15 {{ $storeSlug === $slug ? 'bg-white/15 text-white' : 'text-white/75' }}">{{ $option['plan'] }}</a>
                @endforeach
            </div>

            <header class="sticky top-0 z-40 border-b border-line bg-white/92 backdrop-blur-xl">
                <div class="mx-auto flex min-h-18 max-w-7xl items-center justify-between gap-4 px-5 sm:px-8">
                    <a href="#beranda" class="flex items-center gap-3">
                        <span class="demo-accent-bg flex size-10 items-center justify-center rounded-xl text-sm font-semibold text-white">{{ $store['initials'] }}</span>
                        <span><span class="block text-lg leading-none font-semibold">{{ $store['store_name'] }}</span><span class="mt-1 block text-[10px] tracking-[0.15em] text-ink-soft uppercase">{{ $store['tagline'] }}</span></span>
                    </a>
                    <nav class="hidden items-center gap-7 text-sm text-ink-soft md:flex">
                        <a href="#beranda" class="transition hover:text-navy">Beranda</a>
                        <a href="#produk" class="transition hover:text-navy">Produk</a>
                        <a href="#fitur" class="transition hover:text-navy">Fitur toko</a>
                    </nav>
                    <button type="button" data-cart-open class="group relative flex cursor-pointer items-center gap-2 rounded-full border border-line bg-white px-4 py-2.5 text-sm font-semibold transition hover:-translate-y-0.5 hover:border-navy/30 hover:shadow-md">
                        Keranjang <span data-cart-count class="demo-accent-bg flex size-6 items-center justify-center rounded-full text-xs text-white">0</span>
                    </button>
                </div>
            </header>

            <main>
                <section id="beranda" class="relative overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy py-20 text-white sm:py-28">
                    @if (isset($store['hero_banner']))
                        <div class="absolute inset-0 bg-cover bg-center opacity-60" style="background-image: url('{{ asset($store['hero_banner']) }}')"></div>
                        <div class="absolute inset-0 bg-linear-to-r from-navy via-navy/85 to-navy/55"></div>
                    @endif
                    <div class="demo-accent-bg absolute -top-28 left-[8%] size-80 rounded-full opacity-20 blur-3xl"></div>
                    <div class="absolute -right-20 -bottom-28 size-96 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-5 lg:grid-cols-[1.1fr_.9fr] sm:px-8">
                        <div>
                            <span class="demo-accent-soft demo-accent inline-flex rounded-full px-4 py-2 text-xs font-semibold tracking-wide uppercase">Koleksi pilihan minggu ini</span>
                            <h1 class="mt-6 max-w-3xl text-4xl leading-[1.08] text-white sm:text-6xl">{{ $store['headline'] }}</h1>
                            <p class="mt-6 max-w-xl text-base leading-7 text-white/75 sm:text-lg">{{ $store['description'] }}</p>
                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="#produk" class="demo-accent-bg inline-flex cursor-pointer items-center justify-center rounded-full px-6 py-3 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:brightness-110 hover:shadow-lg">Belanja sekarang</a>
                                <a href="#fitur" class="inline-flex cursor-pointer items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-white/10">Lihat fitur paket</a>
                            </div>
                        </div>
                        <div class="relative mx-auto w-full max-w-xl overflow-hidden rounded-[1.75rem] border border-white/15 bg-white/10 p-4 shadow-2xl backdrop-blur-sm">
                            <div class="flex aspect-[4/3] items-center justify-center rounded-2xl text-9xl" style="background: {{ $bestSeller['color'] }}">{{ $bestSeller['icon'] }}</div>
                            <div class="absolute inset-x-4 bottom-4 h-2/3 rounded-b-2xl bg-linear-to-t from-navy via-navy/35 to-transparent"></div>
                            <div class="absolute right-8 bottom-8 left-8 flex items-end justify-between gap-4">
                                <div><span class="demo-accent-bg inline-flex rounded-full px-3 py-1.5 text-[10px] font-semibold tracking-wide text-white uppercase">Paling laku</span><p class="mt-3 text-lg font-semibold text-white">{{ $bestSeller['name'] }}</p><p class="mt-1 text-sm text-white/70">Rp{{ number_format($bestSeller['price'], 0, ',', '.') }}</p></div>
                                <button type="button" data-add-product="{{ $bestSeller['name'] }}" aria-label="Tambahkan {{ $bestSeller['name'] }} ke keranjang" class="demo-accent-bg flex size-12 shrink-0 cursor-pointer items-center justify-center rounded-full text-2xl font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-1 hover:brightness-110">+</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="produk" class="mx-auto max-w-7xl px-5 py-20 sm:px-8 sm:py-24">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div><p class="demo-accent text-xs font-semibold tracking-[0.18em] uppercase">{{ $store['category'] }}</p><h2 class="mt-3 text-3xl sm:text-4xl">Produk unggulan</h2><p class="mt-3 text-ink-soft">Klik tambah ke keranjang untuk mencoba interaksi toko.</p></div>
                        <span class="rounded-full border border-line bg-white px-5 py-2.5 text-sm font-semibold">{{ count($store['products']) }} produk demo</span>
                    </div>
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($store['products'] as $product)
                            <article class="group overflow-hidden rounded-card border border-line bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex aspect-[4/3] items-center justify-center text-7xl transition duration-300 group-hover:scale-105" style="background: {{ $product['color'] }}">{{ $product['icon'] }}</div>
                                <div class="p-5"><p class="demo-accent text-xs font-semibold tracking-wide uppercase">{{ $product['category'] }}</p><h3 class="mt-2 text-xl">{{ $product['name'] }}</h3><div class="mt-5 flex items-center justify-between gap-3"><p class="text-lg font-semibold">Rp{{ number_format($product['price'], 0, ',', '.') }}</p><button type="button" data-add-product="{{ $product['name'] }}" class="demo-accent-bg cursor-pointer rounded-full px-4 py-2.5 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:brightness-110 hover:shadow-md">+ Keranjang</button></div></div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="fitur" class="border-y border-line bg-white">
                    <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8">
                        <div class="grid gap-8 lg:grid-cols-[.85fr_1.15fr] lg:items-center">
                            <div><span class="demo-accent-soft demo-accent inline-flex rounded-full px-4 py-2 text-xs font-semibold uppercase">Paket {{ $store['plan'] }}</span><h2 class="mt-5 text-3xl">Teknologi toko di balik demo ini</h2><p class="mt-4 leading-7 text-ink-soft">Tampilan ini merupakan contoh toko yang dapat dipresentasikan kepada calon klien. Data transaksi di sini hanya simulasi tampilan dan tidak masuk ke billing.</p></div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ([['label' => 'Kapasitas katalog', 'value' => $store['capacity']], ['label' => 'Pembayaran', 'value' => $store['payment']], ['label' => 'Alamat toko', 'value' => $store['domain']], ['label' => 'Biaya bulanan', 'value' => 'Rp'.number_format($store['price'], 0, ',', '.')]] as $feature)
                                    <div class="rounded-card border border-line bg-offwhite p-5"><p class="text-sm text-ink-soft">{{ $feature['label'] }}</p><p class="mt-2 font-semibold">{{ $feature['value'] }}</p></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="bg-navy px-5 py-10 text-sm text-white/65"><div class="mx-auto flex max-w-7xl flex-col justify-between gap-3 sm:flex-row"><p class="font-semibold text-white">{{ $store['store_name'] }}</p><p>Demo ScriptMedia · Paket {{ $store['plan'] }}</p></div></footer>

            <div data-toast class="pointer-events-none fixed right-5 bottom-5 z-50 translate-y-4 rounded-xl bg-navy px-5 py-3 text-sm font-semibold text-white opacity-0 shadow-xl transition duration-200">Produk ditambahkan ke keranjang</div>
            <div data-cart-panel class="pointer-events-none fixed inset-0 z-50 opacity-0 transition" aria-hidden="true">
                <button type="button" data-cart-close class="absolute inset-0 cursor-pointer bg-navy/45" aria-label="Tutup keranjang"></button>
                <aside class="absolute top-0 right-0 flex h-full w-full translate-x-full flex-col bg-white p-6 shadow-2xl transition duration-300 sm:max-w-md" data-cart-drawer>
                    <div class="flex items-center justify-between"><h2 class="text-2xl">Keranjang demo</h2><button type="button" data-cart-close class="cursor-pointer rounded-full border border-line px-3 py-2 text-sm transition hover:bg-offwhite">Tutup</button></div>
                    <div data-cart-items class="mt-8 flex-1 space-y-3 text-sm text-ink-soft"><p>Keranjang masih kosong.</p></div>
                    <div class="border-t border-line pt-5"><button type="button" data-demo-checkout class="demo-accent-bg w-full cursor-pointer rounded-full px-5 py-3 font-semibold text-white transition hover:-translate-y-0.5 hover:brightness-110 hover:shadow-md">Lanjut ke checkout demo</button><p class="mt-3 text-center text-xs text-ink-soft">Tidak ada transaksi sungguhan pada mode demo.</p></div>
                </aside>
            </div>
        </div>

        <script>
            (() => {
                const root = document.querySelector('#demo-store');
                const key = `scriptmedia-demo-cart-${root.dataset.store}`;
                let items = JSON.parse(localStorage.getItem(key) || '[]');
                const counters = document.querySelectorAll('[data-cart-count]');
                const panel = document.querySelector('[data-cart-panel]');
                const drawer = document.querySelector('[data-cart-drawer]');
                const list = document.querySelector('[data-cart-items]');
                const toast = document.querySelector('[data-toast]');
                let toastTimer;

                const render = () => {
                    counters.forEach((counter) => counter.textContent = items.length);
                    list.innerHTML = items.length
                        ? items.map((name, index) => `<div class="flex items-center justify-between rounded-xl bg-offwhite px-4 py-3"><span>${name}</span><button type="button" data-remove="${index}" class="cursor-pointer font-semibold text-red-600">Hapus</button></div>`).join('')
                        : '<p>Keranjang masih kosong.</p>';
                    localStorage.setItem(key, JSON.stringify(items));
                };

                document.querySelectorAll('[data-add-product]').forEach((button) => button.addEventListener('click', () => {
                    items.push(button.dataset.addProduct);
                    render();
                    toast.classList.remove('translate-y-4', 'opacity-0');
                    clearTimeout(toastTimer);
                    toastTimer = setTimeout(() => toast.classList.add('translate-y-4', 'opacity-0'), 1800);
                }));
                document.querySelector('[data-cart-open]').addEventListener('click', () => {
                    panel.classList.remove('pointer-events-none', 'opacity-0');
                    panel.setAttribute('aria-hidden', 'false');
                    drawer.classList.remove('translate-x-full');
                });
                document.querySelectorAll('[data-cart-close]').forEach((button) => button.addEventListener('click', () => {
                    panel.classList.add('pointer-events-none', 'opacity-0');
                    panel.setAttribute('aria-hidden', 'true');
                    drawer.classList.add('translate-x-full');
                }));
                list.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-remove]');
                    if (!button) return;
                    items.splice(Number(button.dataset.remove), 1);
                    render();
                });
                document.querySelector('[data-demo-checkout]').addEventListener('click', () => {
                    toast.textContent = items.length
                        ? 'Checkout demo siap — transaksi asli sengaja dinonaktifkan.'
                        : 'Tambahkan produk terlebih dahulu.';
                    toast.classList.remove('translate-y-4', 'opacity-0');
                    clearTimeout(toastTimer);
                    toastTimer = setTimeout(() => toast.classList.add('translate-y-4', 'opacity-0'), 2400);
                });
                render();
            })();
        </script>
    </body>
</html>
