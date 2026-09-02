<x-layouts::public title="Sewa Toko Online">
    <main>
        <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy text-white">
            <div class="absolute -top-24 right-0 size-96 rounded-full bg-orange/20 blur-3xl"></div>
            <div class="absolute -bottom-32 left-0 size-96 rounded-full bg-tosca/25 blur-3xl"></div>
            <div class="relative mx-auto grid max-w-7xl gap-12 px-5 py-20 sm:px-8 sm:py-28 lg:grid-cols-[1.15fr_.85fr] lg:items-center">
                <div>
                    <p class="text-sm font-semibold tracking-[0.22em] text-orange uppercase">Sewa web e-commerce siap jualan</p>
                    <h1 class="mt-5 max-w-3xl text-4xl leading-tight font-semibold sm:text-6xl">Punya toko online profesional tanpa repot urus teknis.</h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-white/75">Pilih paket, lakukan pembayaran, lalu tim ScriptMedia menyiapkan toko dan kredensial admin untuk bisnismu.</p>
                    <div class="mt-8 flex flex-wrap gap-3"><x-ui.button href="#paket">Lihat pilihan paket</x-ui.button><a href="{{ route('guide') }}" class="rounded-full border border-white/30 px-6 py-3 text-sm font-semibold hover:bg-white/10">Panduan lengkap</a></div>
                </div>
                <div class="rounded-card border border-white/15 bg-white/10 p-7 shadow-2xl backdrop-blur sm:p-9">
                    <p class="text-sm text-white/65">Termasuk di semua paket</p>
                    <ul class="mt-5 space-y-4 text-sm leading-6">
                        <li class="flex gap-3"><span class="text-orange">✓</span><span>Platform toko, hosting, SSL, dan monitoring</span></li>
                        <li class="flex gap-3"><span class="text-orange">✓</span><span>Pembayaran otomatis melalui Midtrans</span></li>
                        <li class="flex gap-3"><span class="text-orange">✓</span><span>Backup, update keamanan, dan bantuan teknis</span></li>
                        <li class="flex gap-3"><span class="text-orange">✓</span><span>Portal untuk memantau pembayaran dan pembuatan toko</span></li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="alur" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
            <div class="text-center"><p class="text-xs font-semibold tracking-[0.22em] text-tosca uppercase">Alur sederhana</p><h2 class="mt-3 text-3xl sm:text-4xl">Dari daftar sampai toko siap digunakan</h2></div>
            <div class="mt-10 grid gap-5 md:grid-cols-4">
                @foreach ([['1','Daftar & pilih plan','Buat akun klien dan tentukan paket yang sesuai.'],['2','Isi data toko','Tentukan nama, subdomain, dan custom domain bila tersedia.'],['3','Bayar aman','Selesaikan pembayaran melalui Midtrans.'],['4','Toko disiapkan','Pantau status dan terima kredensial setelah selesai.']] as [$number, $title, $description])
                    <div class="rounded-card border border-line bg-white p-6 shadow-card"><span class="flex size-10 items-center justify-center rounded-full bg-orange text-sm font-semibold text-navy">{{ $number }}</span><h3 class="mt-5 text-xl">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-ink-soft">{{ $description }}</p></div>
                @endforeach
            </div>
        </section>

        <section id="paket" class="bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div class="text-center"><p class="text-xs font-semibold tracking-[0.22em] text-tosca uppercase">Pilih paket</p><h2 class="mt-3 text-3xl sm:text-4xl">Mulai dari kebutuhan tokomu sekarang</h2><p class="mt-3 text-ink-soft">Bayar 10 bulan untuk pemakaian 12 bulan pada periode tahunan.</p></div>
                <div class="mt-10 grid gap-6 lg:grid-cols-3">
                    @forelse ($plans as $plan)
                        @php
                            $isStandard = $plan->name === 'standard';
                            $startUrl = auth()->check() ? route('onboarding.create', $plan) : route('register', ['plan' => $plan->slug]);
                        @endphp
                        <article class="relative flex rounded-card border {{ $isStandard ? 'border-orange ring-2 ring-orange/20' : 'border-line' }} bg-offwhite p-7 shadow-card">
                            @if ($isStandard)<span class="absolute -top-3 left-6 rounded-full bg-orange px-4 py-1 text-xs font-semibold text-navy">Paling populer</span>@endif
                            <div class="flex w-full flex-col">
                                <p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">{{ $plan->name }}</p>
                                <p class="mt-4 text-4xl font-semibold">Rp{{ number_format($plan->monthlyTotal(), 0, ',', '.') }}<span class="text-sm font-normal text-ink-soft"> / bulan</span></p>
                                <p class="mt-2 text-sm text-ink-soft">Tanpa biaya aktivasi</p>
                                <ul class="mt-6 space-y-3 text-sm leading-6 text-ink-soft">
                                    <li>✓ {{ $plan->max_products ? 'Hingga '.$plan->max_products.' produk' : 'Produk unlimited' }}</li>
                                    <li>✓ {{ $plan->max_payment_gateways ?? 'Multi' }} payment gateway</li>
                                    <li>✓ {{ $plan->custom_domain_allowed ? 'Mendukung custom domain' : 'Subdomain ScriptMedia' }}</li>
                                    <li>✓ {{ $plan->content_request_quota }}× request perubahan konten</li>
                                    <li>✓ Respons dukungan maks. {{ $plan->support_sla_hours }} jam</li>
                                </ul>
                                <x-ui.button :href="$startUrl" :variant="$isStandard ? 'orange' : 'navy'" class="mt-8 w-full">Pilih {{ str($plan->name)->title() }}</x-ui.button>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-card border border-line bg-offwhite p-8 text-center text-ink-soft lg:col-span-3">Paket sedang disiapkan. Silakan hubungi tim ScriptMedia.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </main>
</x-layouts::public>
