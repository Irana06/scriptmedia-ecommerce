<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-offwhite antialiased">
        <main class="relative flex min-h-svh items-center justify-center overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy p-6 sm:p-10">
            <div class="absolute -top-24 -right-20 size-80 rounded-full bg-orange/20 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-16 size-96 rounded-full bg-tosca/20 blur-3xl"></div>

            <div class="relative w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-7 flex items-center justify-center gap-3 text-white" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-orange text-navy">
                        <x-app-logo-icon class="size-6 fill-current" />
                    </span>
                    <span>
                        <span class="block text-xl leading-none font-semibold">ScriptMedia</span>
                        <span class="mt-1 block text-[10px] tracking-[0.24em] text-white/65 uppercase">Toko Panel</span>
                    </span>
                </a>

                <div data-auth-card class="rounded-card border border-white/20 bg-white p-7 text-navy shadow-2xl shadow-navy/30 sm:p-9">
                    {{ $slot }}
                </div>

                <p class="mt-5 text-center text-xs tracking-wide text-white/60">
                    Area terbatas untuk tim ScriptMedia dan owner tenant.
                </p>
            </div>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
