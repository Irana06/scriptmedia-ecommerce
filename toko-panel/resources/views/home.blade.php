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
                            $highlights = match ($plan->slug) {
                                'starter' => [
                                    'Template siap pakai, hingga '.$plan->max_products.' produk',
                                    $plan->max_payment_gateways.' payment gateway otomatis (QRIS otomatis)',
                                    'Backup mingguan dan update keamanan dasar',
                                    $plan->content_request_quota.'× request ubah konten kecil / bulan',
                                ],
                                'standard' => [
                                    'Hingga '.$plan->max_products.' produk, mendukung custom domain',
                                    $plan->max_payment_gateways.' payment gateway otomatis (QRIS + transfer bank)',
                                    'Dukungan maks. '.$plan->support_sla_hours.' jam dan laporan bulanan',
                                    $plan->content_request_quota.'× request ubah konten / bulan',
                                ],
                                'pro' => [
                                    'Produk unlimited dan multi payment gateway',
                                    'Ongkir otomatis real-time dan kustomisasi desain penuh',
                                    'Dukungan maks. '.$plan->support_sla_hours.' jam, termasuk akhir pekan',
                                    $plan->content_request_quota.'× request ubah konten / bulan',
                                ],
                                default => [],
                            };
                            $platformDetails = match ($plan->slug) {
                                'starter' => ['1 pilihan desain template toko', 'Hingga '.$plan->max_products.' produk', 'QRIS otomatis'],
                                'standard' => ['Semua fitur Starter', 'Hingga '.$plan->max_products.' produk', 'QRIS dan transfer bank otomatis', 'Penyimpanan gambar produk lebih besar'],
                                'pro' => ['Semua fitur Standard', 'Kapasitas produk unlimited', 'QRIS, transfer bank, kartu kredit, dan e-wallet', 'Integrasi ongkir otomatis real-time'],
                                default => [],
                            };
                            $careDetails = match ($plan->slug) {
                                'starter' => ['Subdomain ScriptMedia dan SSL otomatis', 'Backup data mingguan', 'Update keamanan dasar dan monitoring uptime', 'Dukungan WhatsApp pada hari kerja'],
                                'standard' => ['Subdomain atau custom domain', 'Backup mingguan + restore atas permintaan', 'Monitoring keamanan dan uptime', 'Laporan bulanan traffic dan aktivitas toko'],
                                'pro' => ['Subdomain atau custom domain + kustomisasi desain penuh', 'Backup mingguan dengan retensi 14 hari', 'Dukungan prioritas termasuk akhir pekan', 'Laporan detail + konsultasi bulanan 30 menit'],
                                default => [],
                            };
                        @endphp
                        <article class="relative flex rounded-card border {{ $isStandard ? 'border-orange ring-2 ring-orange/20' : 'border-line' }} bg-offwhite p-7 shadow-card">
                            @if ($isStandard)<span class="absolute -top-3 left-6 rounded-full bg-orange px-4 py-1 text-xs font-semibold text-navy">Paling populer</span>@endif
                            <div class="flex w-full flex-col">
                                <p class="text-xs font-semibold tracking-[0.2em] text-tosca uppercase">{{ $plan->name }}</p>
                                <p class="mt-4 text-4xl font-semibold">Rp{{ number_format($plan->monthlyTotal(), 0, ',', '.') }}<span class="text-sm font-normal text-ink-soft"> / bulan</span></p>
                                <div class="mt-4 space-y-2 rounded-xl border border-line bg-white p-4 text-sm text-ink-soft">
                                    <div class="flex justify-between gap-4"><span>Sewa Platform</span><strong class="text-navy">Rp{{ number_format((float) $plan->price_platform, 0, ',', '.') }}</strong></div>
                                    <div class="flex justify-between gap-4"><span>Web Care Plan</span><strong class="text-navy">Rp{{ number_format((float) $plan->price_care_monthly, 0, ',', '.') }}</strong></div>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-navy">Tanpa biaya aktivasi</p>
                                <p class="mt-1 text-xs leading-5 text-ink-soft">Tahunan Rp{{ number_format($plan->annualTotal(), 0, ',', '.') }} · hemat setara 2 bulan</p>
                                <ul class="mt-6 space-y-3 text-sm leading-6 text-ink-soft">
                                    @foreach ($highlights as $highlight)<li>✓ {{ $highlight }}</li>@endforeach
                                </ul>
                                <details class="mt-6 border-t border-line pt-5 text-sm text-ink-soft">
                                    <summary class="cursor-pointer font-semibold text-navy">Apa saja yang termasuk?</summary>
                                    <div class="mt-4 space-y-4 leading-6">
                                        <div><p class="font-semibold text-tosca">Sewa Platform mencakup</p><ul class="mt-2 space-y-1">@foreach ($platformDetails as $detail)<li>• {{ $detail }}</li>@endforeach</ul></div>
                                        <div><p class="font-semibold text-tosca">Web Care Plan mencakup</p><ul class="mt-2 space-y-1">@foreach ($careDetails as $detail)<li>• {{ $detail }}</li>@endforeach</ul></div>
                                    </div>
                                </details>
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
