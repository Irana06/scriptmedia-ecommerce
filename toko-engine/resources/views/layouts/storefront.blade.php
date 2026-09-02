@props(['title' => null])

@php
    $storeSetting = \App\Models\StoreSetting::query()->first();
    $storeName = $storeSetting?->store_name ?? 'Toko Senja';
    $logoUrl = $storeSetting?->getFirstMediaUrl('logo');
    $cartCount = app(\App\Services\CartService::class)->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-offwhite">
        <header class="sticky top-0 z-40 border-b border-line bg-white/90 backdrop-blur-lg">
            <div class="mx-auto flex min-h-18 max-w-7xl items-center justify-between gap-4 px-5 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Beranda {{ $storeName }}">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo {{ $storeName }}" class="size-10 rounded-xl object-cover">
                    @else
                        <span class="flex size-10 items-center justify-center rounded-xl bg-navy text-sm font-semibold text-orange">{{ \Illuminate\Support\Str::initials($storeName) }}</span>
                    @endif
                    <span>
                        <span class="block text-lg leading-none font-semibold text-navy">{{ $storeName }}</span>
                        <span class="mt-1 block text-[10px] tracking-[0.18em] text-tosca uppercase">{{ $storeSetting?->tagline ?? 'Pilihan lokal' }}</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-7 text-sm text-ink-soft md:flex" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}" class="transition hover:text-navy">Beranda</a>
                    <a href="{{ route('products.index') }}" class="transition hover:text-navy">Produk</a>
                    <a href="{{ route('cart.index') }}" class="transition hover:text-navy">Keranjang ({{ $cartCount }})</a>
                </nav>

                <div class="flex items-center gap-2">
                    <details class="relative md:hidden"><summary class="cursor-pointer list-none rounded-full border border-line px-3 py-2 text-xs font-semibold text-navy">Menu</summary><nav class="absolute top-12 right-0 grid min-w-44 gap-1 rounded-xl border border-line bg-white p-2 text-sm text-navy shadow-card"><a href="{{ route('home') }}" class="rounded-lg px-3 py-2 hover:bg-offwhite">Beranda</a><a href="{{ route('products.index') }}" class="rounded-lg px-3 py-2 hover:bg-offwhite">Produk</a><a href="{{ route('cart.index') }}" class="rounded-lg px-3 py-2 hover:bg-offwhite">Keranjang ({{ $cartCount }})</a></nav></details>
                    @auth
                        <x-ui.button :href="route('admin.dashboard')" variant="navy" class="px-4 py-2.5">Admin</x-ui.button>
                    @else
                        <x-ui.button :href="route('login')" variant="navy" class="px-4 py-2.5">Masuk</x-ui.button>
                    @endauth
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-auto mt-5 max-w-7xl px-5 sm:px-8">
                <div class="rounded-xl border border-tosca/30 bg-tosca-tint px-4 py-3 text-sm text-navy">{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-auto mt-5 max-w-7xl px-5 sm:px-8">
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            </div>
        @endif

        <main>{{ $slot }}</main>

        <footer class="border-t border-line bg-white">
            <div class="mx-auto grid max-w-7xl gap-6 px-5 py-10 text-sm text-ink-soft sm:grid-cols-2 sm:px-8">
                <div><p class="text-lg font-semibold text-navy">{{ $storeName }}</p><p class="mt-2 max-w-md">{{ $storeSetting?->tagline ?? 'Produk lokal yang dibuat dengan cerita.' }}</p></div>
                <div class="sm:text-right"><p>{{ $storeSetting?->contact_email }}</p><p class="mt-1">{{ $storeSetting?->phone }}</p><p class="mt-4">&copy; {{ date('Y') }} {{ $storeName }}.</p></div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
