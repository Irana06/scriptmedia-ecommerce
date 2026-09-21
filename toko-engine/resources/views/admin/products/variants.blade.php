@php
    $existingVariants = ($product ?? null)?->variants ?? collect();
    $existingGroups = array_keys(($product ?? null)?->optionGroups() ?? []);
    $optionNames = old('option_names', [$existingGroups[0] ?? '', $existingGroups[1] ?? '']);
    $variantRows = old('variants', $existingVariants->map(function ($variant) use ($existingGroups) {
        $pairs = $variant->optionPairs();

        return [
            'id' => $variant->id,
            'values' => [
                $existingGroups[0] ?? null ? ($pairs[$existingGroups[0]] ?? '') : '',
                ($existingGroups[1] ?? null) ? ($pairs[$existingGroups[1]] ?? '') : '',
            ],
            'price' => (string) (int) $variant->price,
            'stock' => (string) $variant->stock,
            'is_active' => $variant->is_active ? '1' : '',
        ];
    })->values()->all());
@endphp

<div
    class="mt-8 border-t border-line pt-6"
    x-data="{
        rows: @js(array_values($variantRows)),
        names: @js(array_values((array) $optionNames)),
        get enabled() { return this.names.some(name => (name ?? '').trim() !== '') },
        add() { this.rows.push({ id: '', values: ['', ''], price: '', stock: '0', is_active: '1' }) },
        remove(index) { this.rows.splice(index, 1) },
    }"
>
    <h2 class="text-lg text-navy">Varian produk</h2>
    <p class="mt-1 text-sm leading-6 text-ink-soft">Isi nama varian kalau produk ini punya pilihan seperti ukuran atau warna. Biarkan kosong kalau produk dijual tanpa varian — harga dan stok di atas yang dipakai.</p>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <label class="grid gap-2 text-sm font-semibold text-navy">Nama varian 1<input type="text" name="option_names[0]" x-model="names[0]" maxlength="50" placeholder="Ukuran" class="rounded-xl border border-line px-4 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold text-navy">Nama varian 2 (opsional)<input type="text" name="option_names[1]" x-model="names[1]" maxlength="50" placeholder="Warna" class="rounded-xl border border-line px-4 py-3 font-normal"></label>
    </div>

    <div x-show="enabled" x-cloak class="mt-6">
        <template x-for="(row, index) in rows" :key="index">
            <div class="mb-3 grid gap-3 rounded-xl border border-line bg-offwhite p-4 sm:grid-cols-[1fr_1fr_1fr_1fr_auto] sm:items-end">
                <input type="hidden" x-bind:name="`variants[${index}][id]`" x-bind:value="row.id">
                <label class="grid gap-1.5 text-xs font-semibold tracking-wide text-ink-soft uppercase">
                    <span x-text="names[0] || 'Varian 1'"></span>
                    <input type="text" x-bind:name="`variants[${index}][values][0]`" x-model="row.values[0]" maxlength="50" class="rounded-lg border border-line px-3 py-2 text-sm font-normal text-navy">
                </label>
                <label class="grid gap-1.5 text-xs font-semibold tracking-wide text-ink-soft uppercase" x-show="(names[1] ?? '').trim() !== ''">
                    <span x-text="names[1] || 'Varian 2'"></span>
                    <input type="text" x-bind:name="`variants[${index}][values][1]`" x-model="row.values[1]" maxlength="50" class="rounded-lg border border-line px-3 py-2 text-sm font-normal text-navy">
                </label>
                <label class="grid gap-1.5 text-xs font-semibold tracking-wide text-ink-soft uppercase">
                    Harga
                    <input type="number" min="0" step="1" x-bind:name="`variants[${index}][price]`" x-model="row.price" class="rounded-lg border border-line px-3 py-2 text-sm font-normal text-navy">
                </label>
                <label class="grid gap-1.5 text-xs font-semibold tracking-wide text-ink-soft uppercase">
                    Stok
                    <input type="number" min="0" x-bind:name="`variants[${index}][stock]`" x-model="row.stock" class="rounded-lg border border-line px-3 py-2 text-sm font-normal text-navy">
                </label>
                <div class="flex items-center gap-3 pb-2">
                    <label class="flex items-center gap-2 text-xs text-ink-soft">
                        <input type="checkbox" value="1" x-bind:name="`variants[${index}][is_active]`" x-bind:checked="row.is_active === '1'" class="accent-tosca">
                        Aktif
                    </label>
                    <button type="button" class="cursor-pointer text-xs font-semibold text-red-600" x-on:click="remove(index)">Hapus</button>
                </div>
            </div>
        </template>

        <button type="button" class="cursor-pointer rounded-full border border-navy/25 px-4 py-2 text-sm font-semibold text-navy transition hover:border-navy" x-on:click="add()">+ Tambah baris varian</button>
        <p class="mt-3 text-xs leading-5 text-ink-soft">Stok dan harga diambil dari baris varian. Varian yang pernah dipesan tidak dihapus, hanya dinonaktifkan, supaya riwayat pesanan tetap utuh.</p>
    </div>

    @error('option_names')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
    @error('variants')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
