<x-layouts::app title="Dashboard">
    <div class="space-y-10">
        <section class="relative overflow-hidden rounded-card bg-linear-to-br from-navy via-navy-mid to-navy px-6 py-9 text-white shadow-xl shadow-navy/15 sm:px-10 sm:py-12">
            <div class="absolute -top-20 -right-16 size-64 rounded-full bg-orange/20 blur-3xl"></div><div class="absolute -bottom-24 left-1/3 size-56 rounded-full bg-tosca/20 blur-3xl"></div>
            <div class="relative max-w-3xl"><x-ui.badge variant="orange">Ringkasan hari ini</x-ui.badge><h1 class="mt-5 text-3xl leading-tight text-white sm:text-5xl">Selamat datang, {{ auth()->user()->name }}.</h1><p class="mt-4 max-w-2xl leading-7 text-white/70">Pantau produk, order, dan performa toko dari satu dashboard.</p></div>
        </section>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.card><x-ui.badge>Produk</x-ui.badge><p class="mt-5 text-4xl font-semibold text-navy">{{ $productCount }}</p><p class="mt-2 text-sm text-ink-soft">Produk dalam katalog</p></x-ui.card>
            <x-ui.card><x-ui.badge variant="orange">Order baru</x-ui.badge><p class="mt-5 text-4xl font-semibold text-navy">{{ $pendingOrderCount }}</p><p class="mt-2 text-sm text-ink-soft">Menunggu diproses</p></x-ui.card>
            <x-ui.card><x-ui.badge variant="navy">Total order</x-ui.badge><p class="mt-5 text-4xl font-semibold text-navy">{{ $orderCount }}</p><p class="mt-2 text-sm text-ink-soft">Sejak toko berjalan</p></x-ui.card>
            <x-ui.card><x-ui.badge>Revenue</x-ui.badge><p class="mt-5 text-2xl font-semibold text-navy">Rp{{ number_format($revenue, 0, ',', '.') }}</p><p class="mt-2 text-sm text-ink-soft">Dari pembayaran lunas</p></x-ui.card>
        </div>

        <x-ui.card><div class="flex items-center justify-between gap-4"><div><h2 class="text-2xl text-navy">Order terbaru</h2><p class="mt-1 text-sm text-ink-soft">Lima order terakhir yang masuk.</p></div>@can('manage orders')<x-ui.button :href="route('admin.orders.index')" variant="navy">Semua order</x-ui.button>@endcan</div>
            <div class="mt-6 overflow-x-auto"><table class="w-full min-w-[36rem] text-left text-sm"><thead class="border-b border-line text-xs tracking-wide text-ink-soft uppercase"><tr><th class="pb-3">Nomor</th><th class="pb-3">Pelanggan</th><th class="pb-3">Total</th><th class="pb-3">Status</th></tr></thead><tbody class="divide-y divide-line">@forelse ($recentOrders as $order)<tr><td class="py-4 font-semibold text-navy">{{ $order->number }}</td><td class="py-4 text-ink-soft">{{ $order->customer_name }}</td><td class="py-4">Rp{{ number_format((float) $order->total, 0, ',', '.') }}</td><td class="py-4"><x-ui.badge variant="{{ $order->status === 'pending' ? 'orange' : 'tosca' }}">{{ ucfirst($order->status) }}</x-ui.badge></td></tr>@empty<tr><td colspan="4" class="py-8 text-center text-ink-soft">Belum ada order.</td></tr>@endforelse</tbody></table></div>
        </x-ui.card>
    </div>
</x-layouts::app>
