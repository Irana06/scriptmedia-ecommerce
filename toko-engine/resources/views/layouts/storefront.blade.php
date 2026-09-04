@props(['title' => null])

@php
    $demoStore = \App\Support\StorefrontContext::store();
    $storeSetting = \App\Models\StoreSetting::query()->first();
    $storeName = $demoStore['store_name'] ?? $storeSetting?->store_name ?? 'Toko Senja';
    $storeTagline = $demoStore['tagline'] ?? $storeSetting?->tagline ?? 'Pilihan lokal';
    $logoUrl = $demoStore ? null : $storeSetting?->getFirstMediaUrl('logo');
    $cartCount = app(\App\Services\CartService::class)->count();
    $demoLayout = $demoStore['layout'] ?? 'default';
    $wishlistCount = $demoLayout === 'editorial' ? app(\App\Services\WishlistService::class)->count() : 0;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="storefront storefront-plan-{{ $demoLayout }} min-h-screen bg-offwhite">
        @if ($demoStore)
            <div class="bg-navy px-5 py-2.5 text-center text-xs text-white sm:text-sm"><span class="font-semibold">Demo paket {{ $demoStore['plan'] }}</span><span class="text-white/65"> · Pilih paket lain:</span>@foreach (config('demo-stores') as $storeSlug => $option)<a href="{{ route('demo.home', $storeSlug) }}" class="ml-2 rounded-full px-2.5 py-1 transition hover:bg-white/15 {{ $storeSlug === \App\Support\StorefrontContext::slug() ? 'bg-white/15 text-white' : 'text-white/75' }}">{{ $option['plan'] }}</a>@endforeach</div>
        @endif
        <header class="sticky top-0 z-40 border-b border-line bg-white/90 backdrop-blur-lg">
            <div class="mx-auto flex min-h-18 max-w-7xl items-center justify-between gap-4 px-5 sm:px-8">
                <a href="{{ \App\Support\StorefrontContext::route('home') }}" class="flex items-center gap-3" aria-label="Beranda {{ $storeName }}">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo {{ $storeName }}" class="size-10 rounded-xl object-cover">
                    @else
                        <span class="flex size-10 items-center justify-center rounded-xl bg-navy text-sm font-semibold text-orange">{{ \Illuminate\Support\Str::initials($storeName) }}</span>
                    @endif
                    <span>
                        <span class="block text-lg leading-none font-semibold text-navy">{{ $storeName }}</span>
                        <span class="mt-1 block text-[10px] tracking-[0.18em] text-tosca uppercase">{{ $storeTagline }}</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-7 text-sm text-ink-soft md:flex" aria-label="Navigasi utama">
                    <a href="{{ \App\Support\StorefrontContext::route('home') }}" class="transition hover:text-navy">{{ $demoLayout === 'editorial' ? 'Atelier' : 'Beranda' }}</a>
                    <a href="{{ \App\Support\StorefrontContext::route('products.index') }}" class="transition hover:text-navy">{{ $demoLayout === 'simple' ? 'Menu' : ($demoLayout === 'editorial' ? 'Collection' : 'Produk') }}</a>
                    @if($demoLayout === 'editorial')<a href="{{ \App\Support\StorefrontContext::route('wishlist.index') }}" class="transition hover:text-navy">Wishlist ({{ $wishlistCount }})</a>@endif
                    <a href="{{ \App\Support\StorefrontContext::route('cart.index') }}" class="transition hover:text-navy">Keranjang ({{ $cartCount }})</a>
                </nav>

                <div class="flex items-center gap-2">
                    <details class="relative md:hidden"><summary class="cursor-pointer list-none rounded-full border border-line px-3 py-2 text-xs font-semibold text-navy">Menu</summary><nav class="absolute top-12 right-0 grid min-w-44 gap-1 rounded-xl border border-line bg-white p-2 text-sm text-navy shadow-card"><a href="{{ \App\Support\StorefrontContext::route('home') }}" class="rounded-lg px-3 py-2 hover:bg-offwhite">Beranda</a><a href="{{ \App\Support\StorefrontContext::route('products.index') }}" class="rounded-lg px-3 py-2 hover:bg-offwhite">Produk</a>@if($demoLayout === 'editorial')<a href="{{ \App\Support\StorefrontContext::route('wishlist.index') }}" class="rounded-lg px-3 py-2 hover:bg-offwhite">Wishlist ({{ $wishlistCount }})</a>@endif<a href="{{ \App\Support\StorefrontContext::route('cart.index') }}" class="rounded-lg px-3 py-2 hover:bg-offwhite">Keranjang ({{ $cartCount }})</a></nav></details>
                    @auth
                        <x-ui.button :href="route('admin.dashboard')" variant="navy" class="px-4 py-2.5">Admin</x-ui.button>
                    @else
                        <x-ui.button :href="route('login')" variant="navy" class="px-4 py-2.5">Masuk</x-ui.button>
                    @endauth
                </div>
            </div>
        </header>

        @if (session('success') || $errors->any())
            <div class="pointer-events-none fixed top-5 right-5 z-[70] flex w-[calc(100%-2.5rem)] max-w-sm flex-col gap-3" aria-live="polite" aria-atomic="true">
                @if (session('success'))
                    <x-ui.toast>{{ session('success') }}</x-ui.toast>
                @endif

                @if ($errors->any())
                    <x-ui.toast variant="error" :duration="6500">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-ui.toast>
                @endif
            </div>
        @endif

        <main>{{ $slot }}</main>

        <footer class="border-t border-line bg-white">
            <div class="mx-auto grid max-w-7xl gap-6 px-5 py-10 text-sm text-ink-soft sm:grid-cols-2 sm:px-8">
                <div><p class="text-lg font-semibold text-navy">{{ $storeName }}</p><p class="mt-2 max-w-md">{{ $storeTagline }}</p></div>
                <div class="sm:text-right"><p>{{ $demoStore ? 'demo@scriptmedia.net' : $storeSetting?->contact_email }}</p><p class="mt-1">{{ $demoStore ? 'Mode demonstrasi' : $storeSetting?->phone }}</p><p class="mt-4">&copy; {{ date('Y') }} {{ $storeName }}.</p></div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
