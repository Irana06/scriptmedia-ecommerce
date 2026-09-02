@props(['title' => null])

@php
    $storeSetting = \App\Models\StoreSetting::query()->first();
    $storeName = $storeSetting?->store_name ?? 'Toko Senja';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>@include('partials.head', ['title' => $title])</head>
    <body class="min-h-screen bg-offwhite">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            <aside class="hidden h-screen flex-col bg-linear-to-b from-navy to-navy-mid px-5 py-6 text-white lg:sticky lg:top-0 lg:flex">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-2" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-orange font-semibold text-navy">{{ \Illuminate\Support\Str::initials($storeName) }}</span>
                    <span><span class="block text-lg leading-none font-semibold">{{ $storeName }}</span><span class="mt-1.5 block text-[10px] tracking-[0.18em] text-white/55 uppercase">Admin toko</span></span>
                </a>

                <nav class="mt-10 space-y-2 text-sm" aria-label="Navigasi admin">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-white/12 font-semibold text-white' : 'text-white/70 hover:bg-white/8 hover:text-white' }}" wire:navigate><span class="size-2 rounded-full bg-orange"></span>Dashboard</a>
                    @can('manage products')
                        <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->routeIs('admin.products.*') ? 'bg-white/12 font-semibold text-white' : 'text-white/70 hover:bg-white/8 hover:text-white' }}" wire:navigate><span class="size-2 rounded-full bg-tosca"></span>Produk</a>
                    @endcan
                    @can('manage orders')
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->routeIs('admin.orders.*') ? 'bg-white/12 font-semibold text-white' : 'text-white/70 hover:bg-white/8 hover:text-white' }}" wire:navigate><span class="size-2 rounded-full bg-orange-light"></span>Order</a>
                    @endcan
                    @can('view reports')
                        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->routeIs('admin.reports.*') ? 'bg-white/12 font-semibold text-white' : 'text-white/70 hover:bg-white/8 hover:text-white' }}" wire:navigate><span class="size-2 rounded-full bg-white/60"></span>Laporan</a>
                    @endcan
                    @can('manage store settings')
                        <a href="{{ route('admin.store-settings.edit') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->routeIs('admin.store-settings.*') ? 'bg-white/12 font-semibold text-white' : 'text-white/70 hover:bg-white/8 hover:text-white' }}" wire:navigate><span class="size-2 rounded-full bg-white/40"></span>Pengaturan toko</a>
                    @endcan
                </nav>

                <div class="mt-auto rounded-card border border-white/12 bg-white/8 p-4">
                    <p class="text-xs tracking-[0.15em] text-orange-light uppercase">{{ auth()->user()->getRoleNames()->first() ?? 'Admin' }}</p>
                    <p class="mt-2 truncate font-semibold">{{ auth()->user()->name }}</p>
                    <p class="mt-1 truncate text-xs text-white/55">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">@csrf<button type="submit" class="text-xs font-semibold text-white/70 hover:text-orange-light cursor-pointer">Keluar &rarr;</button></form>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="border-b border-line bg-white px-5 sm:px-8">
                    <div class="flex min-h-18 items-center justify-between">
                        <div><p class="text-[10px] tracking-[0.18em] text-tosca uppercase">Admin toko</p><p class="mt-1 font-semibold text-navy">{{ $title ?? 'Dashboard' }}</p></div>
                        <div class="flex items-center gap-3"><x-ui.button :href="route('home')" variant="navy" class="px-4 py-2.5">Lihat toko</x-ui.button><span class="hidden size-10 items-center justify-center rounded-full bg-tosca-tint text-sm font-semibold text-tosca sm:flex">{{ auth()->user()->initials() }}</span></div>
                    </div>
                    <nav class="flex gap-5 overflow-x-auto pb-3 text-xs font-semibold text-ink-soft lg:hidden">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        @can('manage products')<a href="{{ route('admin.products.index') }}">Produk</a>@endcan
                        @can('manage orders')<a href="{{ route('admin.orders.index') }}">Order</a>@endcan
                        @can('view reports')<a href="{{ route('admin.reports.index') }}">Laporan</a>@endcan
                        @can('manage store settings')<a href="{{ route('admin.store-settings.edit') }}">Pengaturan</a>@endcan
                        <a href="{{ route('profile.edit') }}">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="cursor-pointer font-semibold">Keluar</button></form>
                    </nav>
                </header>

                <main class="mx-auto w-full max-w-7xl px-5 py-8 sm:px-8 sm:py-10">
                    @if (session('success'))<div class="mb-6 rounded-xl border border-tosca/30 bg-tosca-tint px-4 py-3 text-sm text-navy">{{ session('success') }}</div>@endif
                    @if ($errors->any())<div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    {{ $slot }}
                </main>
            </div>
        </div>

        @persist('toast')<flux:toast.group><flux:toast /></flux:toast.group>@endpersist
        @fluxScripts
    </body>
</html>
