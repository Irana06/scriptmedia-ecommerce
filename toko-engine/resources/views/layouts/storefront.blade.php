@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-offwhite">
        <header class="sticky top-0 z-40 border-b border-line bg-white/90 backdrop-blur-lg">
            <div class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-6 px-5 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Beranda Toko Senja">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-navy text-sm font-semibold text-orange">TS</span>
                    <span>
                        <span class="block text-lg leading-none font-semibold text-navy">Toko Senja</span>
                        <span class="mt-1 block text-[10px] tracking-[0.18em] text-tosca uppercase">Pilihan lokal</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-7 text-sm text-ink-soft md:flex" aria-label="Navigasi utama">
                    <a href="#koleksi" class="transition hover:text-navy">Koleksi</a>
                    <a href="#keunggulan" class="transition hover:text-navy">Keunggulan</a>
                    <a href="#tentang" class="transition hover:text-navy">Tentang kami</a>
                </nav>

                @auth
                    <x-ui.button :href="route('dashboard')" variant="navy" class="px-4 py-2.5">Admin toko</x-ui.button>
                @else
                    <x-ui.button :href="route('login')" variant="navy" class="px-4 py-2.5">Masuk</x-ui.button>
                @endauth
            </div>
        </header>

        <main>{{ $slot }}</main>

        <footer id="tentang" class="border-t border-line bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-ink-soft sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <p><span class="font-semibold text-navy">Toko Senja</span> · Contoh storefront toko-engine.</p>
                <p>&copy; {{ date('Y') }} Semua hak dilindungi.</p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
