<x-layouts::app title="Pengaturan toko">
    <div>
        <x-ui.badge>Brand toko</x-ui.badge>
        <h1 class="mt-3 text-3xl text-navy">Pengaturan toko</h1>
        <p class="mt-2 text-ink-soft">Atur identitas dasar yang tampil di storefront.</p>
    </div>

    <form method="POST" action="{{ route('admin.store-settings.update') }}" enctype="multipart/form-data" class="mt-8 grid gap-6 lg:grid-cols-[1fr_22rem]">
        @csrf
        @method('PUT')
        <x-ui.card>
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-navy sm:col-span-2">Nama toko<input name="store_name" value="{{ old('store_name', $storeSetting->store_name) }}" required class="rounded-xl border border-line px-4 py-3 font-normal"></label>
                <label class="grid gap-2 text-sm font-semibold text-navy sm:col-span-2">Tagline<input name="tagline" value="{{ old('tagline', $storeSetting->tagline) }}" class="rounded-xl border border-line px-4 py-3 font-normal"></label>
                <label class="grid gap-2 text-sm font-semibold text-navy">Email<input type="email" name="contact_email" value="{{ old('contact_email', $storeSetting->contact_email) }}" class="rounded-xl border border-line px-4 py-3 font-normal"></label>
                <label class="grid gap-2 text-sm font-semibold text-navy">Telepon<input name="phone" value="{{ old('phone', $storeSetting->phone) }}" class="rounded-xl border border-line px-4 py-3 font-normal"></label>
                <label class="grid gap-2 text-sm font-semibold text-navy sm:col-span-2">Alamat<textarea name="address" rows="5" class="rounded-xl border border-line px-4 py-3 font-normal">{{ old('address', $storeSetting->address) }}</textarea></label>
            </div>
        </x-ui.card>
        <div class="space-y-6">
            <x-ui.card>
                <h2 class="text-lg text-navy">Logo</h2>
                @if ($storeSetting->getFirstMediaUrl('logo'))<img src="{{ $storeSetting->getFirstMediaUrl('logo') }}" alt="" class="mt-4 size-24 rounded-xl object-cover">@endif
                <input type="file" name="logo" accept="image/*" class="mt-4 block w-full text-sm text-ink-soft">
            </x-ui.card>
            <x-ui.card>
                <h2 class="text-lg text-navy">Banner</h2>
                @if ($storeSetting->getFirstMediaUrl('banner'))<img src="{{ $storeSetting->getFirstMediaUrl('banner') }}" alt="" class="mt-4 aspect-video w-full rounded-xl object-cover">@endif
                <input type="file" name="banner" accept="image/*" class="mt-4 block w-full text-sm text-ink-soft">
            </x-ui.card>
            <x-ui.button type="submit" class="w-full">Simpan pengaturan</x-ui.button>
        </div>
    </form>

    <section class="mt-10">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <x-ui.badge>Payment</x-ui.badge>
                <h2 class="mt-3 text-2xl text-navy">Payment gateway</h2>
                <p class="mt-2 text-sm text-ink-soft">Aktifkan metode pembayaran yang tersedia saat checkout.</p>
            </div>
            <p class="text-sm text-ink-soft">Limit: {{ $gatewayLimit === null ? 'Unlimited' : $gatewayLimit.' gateway aktif' }}</p>
        </div>

        @error('gateway_limit')<div class="mt-5 rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-700">{{ $message }}</div>@enderror

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @forelse ($gateways as $gateway)
                <x-ui.card>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-navy">{{ $gateway->name }}</h3>
                            <p class="mt-1 text-sm text-ink-soft">{{ $gateway->instructions }}</p>
                        </div>
                        <x-ui.badge variant="{{ $gateway->is_active ? 'tosca' : 'navy' }}">{{ $gateway->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                    </div>
                    <form method="POST" action="{{ route('admin.payment-gateways.update', $gateway) }}" class="mt-5">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="{{ $gateway->is_active ? 0 : 1 }}">
                        <x-ui.button type="submit" variant="navy">{{ $gateway->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</x-ui.button>
                    </form>
                </x-ui.card>
            @empty
                <x-ui.card><p class="text-sm text-ink-soft">Belum ada payment gateway yang dikonfigurasi.</p></x-ui.card>
            @endforelse
        </div>
    </section>
</x-layouts::app>
