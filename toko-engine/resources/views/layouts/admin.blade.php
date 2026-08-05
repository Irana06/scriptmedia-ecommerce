@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-offwhite">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            <aside class="hidden h-screen flex-col bg-linear-to-b from-navy to-navy-mid px-5 py-6 text-white lg:sticky lg:top-0 lg:flex">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-orange font-semibold text-navy">TS</span>
                    <span>
                        <span class="block text-lg leading-none font-semibold">Toko Senja</span>
                        <span class="mt-1.5 block text-[10px] tracking-[0.18em] text-white/55 uppercase">Admin toko</span>
                    </span>
                </a>

                <nav class="mt-10 space-y-2 text-sm" aria-label="Navigasi admin">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl bg-white/12 px-4 py-3 font-semibold text-white" wire:navigate>
                        <span class="size-2 rounded-full bg-orange"></span> Dashboard
                    </a>
                    <a href="#produk" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/70 transition hover:bg-white/8 hover:text-white">
                        <span class="size-2 rounded-full bg-tosca"></span> Produk
                    </a>
                    <a href="#pesanan" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/70 transition hover:bg-white/8 hover:text-white">
                        <span class="size-2 rounded-full bg-orange-light"></span> Pesanan
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/70 transition hover:bg-white/8 hover:text-white" wire:navigate>
                        <span class="size-2 rounded-full bg-white/50"></span> Pengaturan
                    </a>
                </nav>

                <div class="mt-auto rounded-card border border-white/12 bg-white/8 p-4">
                    <p class="text-xs tracking-[0.15em] text-orange-light uppercase">Masuk sebagai</p>
                    <p class="mt-2 truncate font-semibold">{{ auth()->user()->name }}</p>
                    <p class="mt-1 truncate text-xs text-white/55">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-white/70 transition hover:text-orange-light">Keluar &rarr;</button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="flex min-h-18 items-center justify-between border-b border-line bg-white px-5 sm:px-8">
                    <div>
                        <p class="text-[10px] tracking-[0.18em] text-tosca uppercase">Admin toko</p>
                        <p class="mt-1 font-semibold text-navy">{{ $title ?? 'Dashboard' }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.button :href="route('home')" variant="navy" class="px-4 py-2.5">Lihat toko</x-ui.button>
                        <span class="hidden size-10 items-center justify-center rounded-full bg-tosca-tint text-sm font-semibold text-tosca sm:flex">
                            {{ auth()->user()->initials() }}
                        </span>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-7xl px-5 py-8 sm:px-8 sm:py-10">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
