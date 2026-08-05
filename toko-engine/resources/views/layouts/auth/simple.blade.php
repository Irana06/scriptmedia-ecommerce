<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-offwhite antialiased">
        <div class="relative flex min-h-svh items-center justify-center overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-3 2xl:py-6">
            <div class="pointer-events-none absolute -top-28 -left-28 size-80 rounded-full bg-tosca/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-24 -bottom-24 size-96 rounded-full bg-orange/15 blur-3xl"></div>

            <main class="relative grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-line bg-white shadow-[0_30px_90px_rgba(11,37,69,0.14)] lg:min-h-[40rem] lg:grid-cols-[1.08fr_0.92fr] 2xl:min-h-[44rem]">
                <section class="relative hidden overflow-hidden bg-linear-to-br from-navy via-navy to-navy-mid p-12 text-white lg:flex lg:flex-col">
                    <div class="pointer-events-none absolute top-16 right-0 size-72 translate-x-1/2 rounded-full border border-white/10"></div>
                    <div class="pointer-events-none absolute top-28 right-12 size-48 rounded-full border border-tosca/25"></div>
                    <div class="pointer-events-none absolute -bottom-28 -left-24 size-80 rounded-full bg-tosca/15 blur-3xl"></div>

                    <a href="{{ route('home') }}" class="relative z-10 inline-flex w-fit items-center gap-3" wire:navigate>
                        <span class="flex size-11 items-center justify-center rounded-2xl bg-white text-navy shadow-lg shadow-navy/20">
                            <x-store-mark class="size-6" />
                        </span>
                        <span>
                            <span class="block text-lg font-semibold tracking-tight">Toko Engine</span>
                            <span class="block text-xs text-white/55">Commerce workspace</span>
                        </span>
                    </a>

                    <div class="relative z-10 my-auto max-w-xl py-12">
                        <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold tracking-[0.14em] text-tosca-tint uppercase backdrop-blur">Area pengelola toko</span>
                        <h1 class="mt-7 text-4xl leading-[1.08] font-semibold tracking-[-0.04em] text-white xl:text-5xl">Satu dashboard untuk toko yang terus tumbuh.</h1>
                        <p class="mt-6 max-w-lg text-base leading-7 text-white/65">Kelola katalog, pesanan, pembayaran, dan performa toko dari ruang kerja yang sederhana dan terpusat.</p>

                        <div class="mt-9 flex flex-wrap gap-3 text-sm text-white/75">
                            <span class="rounded-full border border-white/12 bg-white/8 px-4 py-2.5">Katalog produk</span>
                            <span class="rounded-full border border-white/12 bg-white/8 px-4 py-2.5">Pesanan real-time</span>
                            <span class="rounded-full border border-white/12 bg-white/8 px-4 py-2.5">Laporan penjualan</span>
                        </div>
                    </div>

                    <div class="relative z-10 flex items-center justify-between gap-6 rounded-card border border-white/10 bg-white/8 p-5 backdrop-blur-sm">
                        <div>
                            <p class="text-xs tracking-[0.14em] text-white/45 uppercase">Workspace aman</p>
                            <p class="mt-2 text-sm text-white/80">Data operasional toko dalam satu akses.</p>
                        </div>
                        <div class="flex items-end gap-1.5" aria-hidden="true">
                            <span class="h-4 w-2 rounded-full bg-white/20"></span>
                            <span class="h-7 w-2 rounded-full bg-white/30"></span>
                            <span class="h-10 w-2 rounded-full bg-tosca"></span>
                            <span class="h-8 w-2 rounded-full bg-orange"></span>
                        </div>
                    </div>
                </section>

                <section class="flex min-h-[40rem] flex-col px-6 py-7 sm:px-10 lg:min-h-0 lg:px-14 lg:py-10">
                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-navy lg:hidden" wire:navigate>
                            <span class="flex size-10 items-center justify-center rounded-xl bg-navy text-white"><x-store-mark class="size-5" /></span>
                            <span class="font-semibold">Toko Engine</span>
                        </a>
                        <a href="{{ route('home') }}" class="ml-auto text-sm font-semibold text-ink-soft transition hover:text-tosca" wire:navigate>&larr; Kembali ke toko</a>
                    </div>

                    <div class="my-auto w-full py-10">
                        {{ $slot }}
                    </div>

                    <p class="text-center text-xs text-ink-soft/70">&copy; {{ now()->year }} Toko Engine. Area khusus pengelola toko.</p>
                </section>
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
