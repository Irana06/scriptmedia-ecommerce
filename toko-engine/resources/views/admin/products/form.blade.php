@php($editing = isset($product))

<div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
    <x-ui.card><div class="grid gap-5 sm:grid-cols-2">
        <label class="grid gap-2 text-sm font-semibold text-navy sm:col-span-2">Nama produk<input name="name" value="{{ old('name', $product->name ?? '') }}" required class="rounded-xl border border-line px-4 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold text-navy">Kategori<select name="category_id" required class="rounded-xl border border-line px-4 py-3 font-normal"><option value="">Pilih kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
        <label class="grid gap-2 text-sm font-semibold text-navy">Harga<input type="number" name="price" value="{{ old('price', $product->price ?? '') }}" min="0" step="1" required class="rounded-xl border border-line px-4 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold text-navy">Stok<input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" min="0" required class="rounded-xl border border-line px-4 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold text-navy sm:col-span-2">Deskripsi<textarea name="description" rows="7" class="rounded-xl border border-line px-4 py-3 font-normal">{{ old('description', $product->description ?? '') }}</textarea></label>
    </div></x-ui.card>
    <div class="space-y-6"><x-ui.card><h2 class="text-lg text-navy">Publikasi</h2><label class="mt-4 flex gap-3 text-sm text-ink-soft"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>Aktif di storefront</label><label class="mt-3 flex gap-3 text-sm text-ink-soft"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false))>Produk unggulan</label></x-ui.card>
        <x-ui.card><h2 class="text-lg text-navy">Gambar produk</h2>@if ($editing && $product->getFirstMediaUrl('product-images'))<img src="{{ $product->getFirstMediaUrl('product-images', 'thumb') }}" alt="" class="mt-4 aspect-video w-full rounded-xl object-cover">@endif<input type="file" name="image" accept="image/*" class="mt-4 block w-full text-sm text-ink-soft"><p class="mt-2 text-xs text-ink-soft">JPG/PNG/WebP, maks. 4 MB.</p></x-ui.card>
        <x-ui.button type="submit" class="w-full">{{ $editing ? 'Simpan perubahan' : 'Buat produk' }}</x-ui.button></div>
</div>
