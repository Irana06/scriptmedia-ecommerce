<x-layouts::app title="Produk">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><x-ui.badge>Katalog</x-ui.badge><h1 class="mt-3 text-3xl text-navy">Manajemen produk</h1><p class="mt-2 text-ink-soft">Kelola harga, stok, kategori, dan gambar produk.</p></div>
        @if ($canAddProduct)<x-ui.button :href="route('admin.products.create')">Tambah produk</x-ui.button>@else<x-ui.button disabled class="cursor-not-allowed opacity-50">Batas produk tercapai</x-ui.button>@endif
    </div>

    @unless ($canAddProduct)<div class="mt-5 rounded-xl border border-orange/30 bg-orange/10 p-4 text-sm text-navy">Paket toko membatasi katalog hingga {{ $productLimit }} produk. Hapus produk atau tingkatkan paket untuk menambah produk baru.</div>@endunless

    <x-ui.card class="mt-8">
        <form method="GET" class="flex gap-3">
            <input name="search" value="{{ $search }}" placeholder="Cari nama produk..." class="min-w-0 flex-1 rounded-xl border border-line px-4 py-3">
            <x-ui.loading-button loading-label="Mencari..." variant="navy">Cari</x-ui.loading-button>
        </form>
    </x-ui.card>

    @if ($products->isEmpty())
        <x-ui.empty-state
            class="mt-5"
            title="{{ $search === '' ? 'Belum ada produk' : 'Produk tidak ditemukan' }}"
            description="{{ $search === '' ? 'Tambahkan produk pertama agar katalog toko mulai terisi.' : 'Coba gunakan kata kunci lain atau tampilkan kembali seluruh produk.' }}"
            :action-href="$search !== '' ? route('admin.products.index') : ($canAddProduct ? route('admin.products.create') : null)"
            :action-label="$search !== '' ? 'Reset pencarian' : ($canAddProduct ? 'Tambah produk pertama' : null)"
        />
    @else
        <x-ui.card class="mt-5" :padding="false">
            <div class="overflow-x-auto"><table class="w-full min-w-[48rem] text-left text-sm"><thead class="border-b border-line bg-offwhite text-xs tracking-wide text-ink-soft uppercase"><tr><th class="px-6 py-4">Produk</th><th class="px-6 py-4">Kategori</th><th class="px-6 py-4">Harga</th><th class="px-6 py-4">Stok</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-line">@foreach ($products as $product)<tr><td class="px-6 py-4 font-semibold text-navy">{{ $product->name }}</td><td class="px-6 py-4 text-ink-soft">{{ $product->category->name }}</td><td class="px-6 py-4">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</td><td class="px-6 py-4">{{ $product->stock }}</td><td class="px-6 py-4"><x-ui.badge variant="{{ $product->is_active ? 'tosca' : 'navy' }}">{{ $product->is_active ? 'Aktif' : 'Draft' }}</x-ui.badge></td><td class="px-6 py-4"><div class="flex justify-end gap-3"><a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-tosca">Edit</a><form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form></div></td></tr>@endforeach</tbody></table></div>
        </x-ui.card>
        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</x-layouts::app>
