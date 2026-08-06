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
                    @role('admin')
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.dashboard') ? 'bg-white/15' : '' }}" wire:navigate>Dashboard</a>
                        <a href="{{ route('admin.plans.index') }}" class="rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.plans.*') ? 'bg-white/15' : '' }}" wire:navigate>Plan</a>
                        <a href="{{ route('admin.tenants.index') }}" class="rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.tenants.*') ? 'bg-white/15' : '' }}" wire:navigate>Tenant</a>
                        <a href="{{ route('admin.billing.index') }}" class="rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.billing.*') ? 'bg-white/15' : '' }}" wire:navigate>Billing</a>
                        <a href="{{ route('admin.content-requests.index') }}" class="rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.content-requests.*') ? 'bg-white/15' : '' }}" wire:navigate>Tiket Konten</a>
                    @elserole('owner')
                        <a href="{{ route('portal.dashboard') }}" class="rounded-full bg-white/12 px-4 py-2 text-sm text-white transition hover:bg-white/20" wire:navigate>Portal Tenant</a>
                    @endrole
                </nav>

                <div class="ml-auto">
                    <x-desktop-user-menu />
                </div>
            </div>

            <nav class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-5 pb-3 md:hidden" aria-label="{{ __('Mobile navigation') }}">
                @role('admin')
                    <a href="{{ route('admin.dashboard') }}" class="shrink-0 rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.dashboard') ? 'bg-white/15' : '' }}" wire:navigate>Dashboard</a>
                    <a href="{{ route('admin.plans.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.plans.*') ? 'bg-white/15' : '' }}" wire:navigate>Plan</a>
                    <a href="{{ route('admin.tenants.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.tenants.*') ? 'bg-white/15' : '' }}" wire:navigate>Tenant</a>
                    <a href="{{ route('admin.billing.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.billing.*') ? 'bg-white/15' : '' }}" wire:navigate>Billing</a>
                    <a href="{{ route('admin.content-requests.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm text-white transition hover:bg-white/15 {{ request()->routeIs('admin.content-requests.*') ? 'bg-white/15' : '' }}" wire:navigate>Tiket Konten</a>
                @elserole('owner')
                    <a href="{{ route('portal.dashboard') }}" class="shrink-0 rounded-full bg-white/12 px-4 py-2 text-sm text-white transition hover:bg-white/20" wire:navigate>Portal Tenant</a>
                @endrole
            </nav>
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
