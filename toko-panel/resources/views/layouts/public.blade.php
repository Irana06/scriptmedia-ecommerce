<!DOCTYPE html>
<html lang="id">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-offwhite text-navy">
        <header class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur">
            <div class="mx-auto flex min-h-18 max-w-7xl items-center gap-4 px-5 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-orange text-navy"><x-app-logo-icon class="size-5 fill-current" /></span>
                    <span><span class="block text-lg leading-none font-semibold">ScriptMedia</span><span class="mt-1 block text-[10px] tracking-[0.22em] text-tosca uppercase">Sewa Toko Online</span></span>
                </a>
                <nav class="ml-auto flex items-center gap-2">
                    @auth
                        <x-ui.button :href="route('dashboard')" variant="navy">Buka panel</x-ui.button>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-sm font-semibold text-navy hover:bg-offwhite">Masuk</a>
                        <x-ui.button :href="route('register')">Daftar</x-ui.button>
                    @endauth
                </nav>
            </div>
        </header>

        {{ $slot }}

        <footer class="border-t border-line bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-8 text-sm text-ink-soft sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <p>© {{ now()->year }} ScriptMedia. Solusi toko online untuk bisnis Indonesia.</p>
                <p>Butuh bantuan? Hubungi tim ScriptMedia.</p>
            </div>
        </footer>
        @fluxScripts
    </body>
</html>
