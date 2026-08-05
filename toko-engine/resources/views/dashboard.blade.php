<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-10">
        <section class="relative overflow-hidden rounded-card bg-linear-to-br from-navy via-navy-mid to-navy px-6 py-9 text-white shadow-xl shadow-navy/15 sm:px-10 sm:py-12">
            <div class="absolute -top-20 -right-16 size-64 rounded-full bg-orange/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-1/3 size-56 rounded-full bg-tosca/20 blur-3xl"></div>
            <div class="relative max-w-3xl">
                <x-ui.badge variant="orange">Ringkasan hari ini</x-ui.badge>
                <h1 class="mt-5 text-3xl leading-tight text-white sm:text-5xl">Selamat datang, {{ auth()->user()->name }}.</h1>
                <p class="mt-4 max-w-2xl leading-7 text-white/70">Pantau produk dan pesanan tokomu melalui tampilan admin yang ringkas dan konsisten.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <x-ui.button href="#produk">Kelola produk</x-ui.button>
                    <x-ui.button :href="route('home')" variant="navy" class="ring-1 ring-white/30">Lihat storefront</x-ui.button>
                </div>
            </div>
        </section>

        <section id="produk">
            <x-ui.section-header
                eyebrow="Performa toko"
                title="Semua yang penting, dalam satu pandangan"
                description="Komponen reusable menjaga badge, card, dan tombol tetap konsisten di seluruh admin toko."
            />

            <div class="mt-8 grid gap-5 md:grid-cols-3">
                <x-ui.card>
                    <div class="flex items-start justify-between"><span class="text-3xl">📦</span><x-ui.badge>Produk</x-ui.badge></div>
                    <p class="mt-6 text-4xl font-semibold text-navy">128</p>
                    <p class="mt-2 text-sm text-ink-soft">6 produk menunggu pembaruan stok.</p>
                </x-ui.card>
                <x-ui.card>
                    <div class="flex items-start justify-between"><span class="text-3xl">🧾</span><x-ui.badge variant="orange">Pesanan</x-ui.badge></div>
                    <p class="mt-6 text-4xl font-semibold text-navy">24</p>
                    <p class="mt-2 text-sm text-ink-soft">8 pesanan baru perlu segera diproses.</p>
                </x-ui.card>
                <x-ui.card>
                    <div class="flex items-start justify-between"><span class="text-3xl">💳</span><x-ui.badge variant="navy">Pendapatan</x-ui.badge></div>
                    <p class="mt-6 text-3xl font-semibold text-navy">Rp8,4 jt</p>
                    <p class="mt-2 text-sm text-ink-soft">Naik 12% dibanding periode sebelumnya.</p>
                </x-ui.card>
            </div>
        </section>

        <x-ui.card id="pesanan">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div><x-ui.badge variant="orange">Perlu tindakan</x-ui.badge><h2 class="mt-3 text-2xl text-navy">Pesanan terbaru</h2><p class="mt-2 text-sm text-ink-soft">Tiga pesanan sudah dibayar dan siap dikemas.</p></div>
                <x-ui.button href="#" variant="navy">Buka daftar pesanan</x-ui.button>
            </div>
        </x-ui.card>
    </div>
</x-layouts::app>
