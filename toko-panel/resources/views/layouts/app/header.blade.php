<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-offwhite">
        <header class="bg-navy text-white shadow-lg shadow-navy/10">
            <div class="mx-auto flex min-h-18 max-w-7xl items-center gap-6 px-5 py-3 sm:px-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3" wire:navigate>
                    <span class="flex size-10 items-center justify-center rounded-xl bg-orange text-navy">
                        <x-app-logo-icon class="size-5 fill-current" />
                    </span>
                    <span>
                        <span class="block text-lg leading-none font-semibold">ScriptMedia</span>
                        <span class="mt-1 block text-[10px] tracking-[0.22em] text-white/65 uppercase">Toko Panel</span>
                    </span>
                </a>

                <nav class="ml-6 hidden items-center gap-1 md:flex" aria-label="{{ __('Primary navigation') }}">
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-full bg-white/12 px-4 py-2 text-sm text-white transition hover:bg-white/20"
                        wire:navigate
                    >
                        {{ __('Dashboard') }}
                    </a>
                </nav>

                <div class="ml-auto">
                    <x-desktop-user-menu />
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-5 py-10 sm:px-8 sm:py-14">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
