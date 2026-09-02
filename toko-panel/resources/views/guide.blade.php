<x-layouts::public title="Panduan Sewa Toko Online">
    <main>
        <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-mid to-navy text-white">
            <div class="absolute -top-24 right-0 size-96 rounded-full bg-orange/20 blur-3xl"></div>
            <div class="absolute -bottom-32 left-0 size-96 rounded-full bg-tosca/25 blur-3xl"></div>
            <div class="relative mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24">
                <div class="max-w-4xl">
                    <p class="text-xs font-semibold tracking-[0.24em] text-orange uppercase">Panduan layanan ScriptMedia</p>
                    <h1 class="mt-5 text-4xl leading-tight font-semibold sm:text-6xl">Satu gambaran utuh dari memilih paket sampai toko mulai menerima order.</h1>
                    <p class="mt-6 max-w-3xl text-lg leading-8 text-white/75">Dokumen ringkas untuk calon klien dan pengambil keputusan yang ingin memahami layanan sewa e-commerce tanpa istilah teknis yang rumit.</p>
                    <div class="mt-8 flex flex-wrap gap-3 print:hidden">
                        <x-ui.button href="#alur">Lihat alur bisnis</x-ui.button>
                        <a href="#perbandingan" class="rounded-full border border-white/30 px-6 py-3 text-sm font-semibold hover:bg-white/10">Bandingkan paket</a>
                        <button type="button" onclick="window.print()" class="rounded-full border border-white/30 px-6 py-3 text-sm font-semibold hover:bg-white/10">Cetak / simpan PDF</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ([
                    ['Toko siap dipakai', 'Klien tidak perlu menyiapkan server, SSL, backup, atau instalasi sendiri.', 'orange'],
                    ['Biaya terukur', 'Paket bulanan sudah menggabungkan platform dan layanan perawatan web.', 'tosca'],
                    ['Didampingi tim', 'Pembuatan toko, keamanan dasar, dan permintaan perubahan konten ditangani sesuai paket.', 'navy'],
                ] as [$title, $description, $color])
                    @php
                        $iconClass = match ($color) {
                            'orange' => 'bg-orange text-navy',
                            'tosca' => 'bg-tosca-tint text-navy',
                            default => 'bg-navy text-white',
                        };
                    @endphp
                    <article class="rounded-card border border-line bg-white p-7 shadow-card">
                        <span class="flex size-11 items-center justify-center rounded-full {{ $iconClass }}">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                        </span>
                        <h2 class="mt-5 text-2xl">{{ $title }}</h2>
                        <p class="mt-3 leading-7 text-ink-soft">{{ $description }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="alur" class="border-y border-line bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div class="max-w-3xl"><p class="text-xs font-semibold tracking-[0.22em] text-tosca uppercase">Alur bisnis</p><h2 class="mt-3 text-3xl sm:text-4xl">Apa yang terjadi setelah calon klien memilih layanan?</h2><p class="mt-4 leading-7 text-ink-soft">Toko Panel menjadi tempat pendaftaran, pembayaran, dan pemantauan. Toko Engine adalah website toko beserta area administrasinya.</p></div>
                <div class="relative mt-12 grid gap-5 lg:grid-cols-6">
                    @foreach ([
                        ['01', 'Daftar akun', 'Klien membuat akun Toko Panel menggunakan email aktif.'],
                        ['02', 'Pilih paket', 'Klien memilih Starter, Standard, atau Pro sesuai kebutuhan.'],
                        ['03', 'Isi data toko', 'Nama bisnis, alamat web yang diinginkan, WhatsApp, dan catatan kebutuhan diisi.'],
                        ['04', 'Bayar', 'Tagihan dibayar melalui payment gateway yang tersedia.'],
                        ['05', 'Tim menyiapkan', 'Admin memverifikasi pembayaran lalu membuat website dan akun pengelola toko.'],
                        ['06', 'Toko aktif', 'Kredensial tersedia di detail order dan dapat disampaikan melalui WhatsApp.'],
                    ] as [$number, $title, $description])
                        <article class="relative rounded-card border border-line bg-offwhite p-5">
                            <span class="text-sm font-semibold text-orange">{{ $number }}</span>
                            <h3 class="mt-3 text-lg">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-ink-soft">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
                <div class="mt-8 rounded-card border border-tosca/25 bg-tosca-tint/55 p-6 sm:p-8">
                    <p class="font-semibold text-navy">Dua akses yang berbeda, tetapi memakai email yang sama</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-white p-5"><p class="text-xs font-semibold tracking-wide text-tosca uppercase">Toko Panel</p><p class="mt-2 text-sm leading-6 text-ink-soft">Untuk melihat order sewa, tagihan, status pembuatan toko, dan permintaan layanan.</p></div>
                        <div class="rounded-xl bg-white p-5"><p class="text-xs font-semibold tracking-wide text-tosca uppercase">Toko Engine</p><p class="mt-2 text-sm leading-6 text-ink-soft">Untuk mengelola produk, stok, pesanan pembeli, pembayaran, dan konten toko.</p></div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-ink-soft">Password awal Toko Engine dibuat otomatis dan wajib diganti saat pertama kali masuk.</p>
                </div>
            </div>
        </section>

        <section id="perbandingan" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
            <div class="text-center"><p class="text-xs font-semibold tracking-[0.22em] text-tosca uppercase">Perbandingan paket</p><h2 class="mt-3 text-3xl sm:text-4xl">Pilih berdasarkan skala dan tingkat pendampingan</h2><p class="mt-4 text-ink-soft">Langganan tahunan membayar 10 bulan untuk akses selama 12 bulan.</p></div>
            <div class="mt-10 overflow-x-auto rounded-card border border-line bg-white shadow-card">
                <table class="w-full min-w-[54rem] text-left text-sm">
                    <thead class="bg-navy text-white">
                        <tr><th class="px-6 py-5">Komponen</th>@foreach ($plans as $plan)<th class="px-6 py-5 text-lg">{{ str($plan->name)->title() }}</th>@endforeach</tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <tr><th class="px-6 py-4 font-semibold text-navy">Biaya bulanan</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-lg font-semibold text-navy">Rp{{ number_format($plan->monthlyTotal(), 0, ',', '.') }}</td>@endforeach</tr>
                        <tr class="bg-offwhite"><th class="px-6 py-4 font-semibold text-navy">Biaya tahunan</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-navy">Rp{{ number_format($plan->annualTotal(), 0, ',', '.') }}</td>@endforeach</tr>
                        <tr><th class="px-6 py-4 font-semibold text-navy">Kapasitas produk</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-ink-soft">{{ $plan->max_products ? 'Hingga '.$plan->max_products : 'Tidak dibatasi' }}</td>@endforeach</tr>
                        <tr class="bg-offwhite"><th class="px-6 py-4 font-semibold text-navy">Payment gateway</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-ink-soft">{{ $plan->max_payment_gateways ?? 'Multi gateway' }}</td>@endforeach</tr>
                        <tr><th class="px-6 py-4 font-semibold text-navy">Alamat website</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-ink-soft">{{ $plan->custom_domain_allowed ? 'Custom domain atau subdomain' : 'Subdomain ScriptMedia' }}</td>@endforeach</tr>
                        <tr class="bg-offwhite"><th class="px-6 py-4 font-semibold text-navy">Perubahan konten</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-ink-soft">{{ $plan->content_request_quota }} permintaan/periode</td>@endforeach</tr>
                        <tr><th class="px-6 py-4 font-semibold text-navy">Respons dukungan</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-ink-soft">Maks. {{ $plan->support_sla_hours }} jam</td>@endforeach</tr>
                        <tr class="bg-offwhite"><th class="px-6 py-4 font-semibold text-navy">Ongkir real-time</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-ink-soft">{{ $plan->allow_realtime_shipping ? 'Tersedia' : 'Belum termasuk' }}</td>@endforeach</tr>
                        <tr><th class="px-6 py-4 font-semibold text-navy">Kustomisasi desain penuh</th>@foreach ($plans as $plan)<td class="px-6 py-4 text-ink-soft">{{ $plan->allow_full_design_customization ? 'Tersedia sesuai kesepakatan' : 'Menggunakan template' }}</td>@endforeach</tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-sm leading-6 text-ink-soft">Harga yang ditampilkan merupakan gabungan biaya platform dan web care. Domain custom mengikuti ketersediaan nama domain dan proses pengarahannya.</p>
        </section>

        <section class="bg-navy py-16 text-white sm:py-20">
            <div class="mx-auto grid max-w-7xl gap-8 px-5 sm:px-8 lg:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold tracking-[0.22em] text-orange uppercase">Setelah toko aktif</p>
                    <h2 class="mt-3 text-3xl sm:text-4xl">Pemilik mengelola toko, pembeli berbelanja tanpa proses rumit.</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-card border border-white/15 bg-white/10 p-6"><h3 class="text-xl">Pemilik toko</h3><p class="mt-3 text-sm leading-6 text-white/70">Masuk ke Toko Engine untuk mengatur produk, stok, pesanan, pembayaran, dan profil toko.</p></div>
                    <div class="rounded-card border border-white/15 bg-white/10 p-6"><h3 class="text-xl">Pembeli</h3><p class="mt-3 text-sm leading-6 text-white/70">Memilih produk dan checkout tanpa wajib membuat akun. Link pribadi dapat dipakai untuk mengecek status order.</p></div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-5xl px-5 py-16 sm:px-8 sm:py-20">
            <div class="text-center"><p class="text-xs font-semibold tracking-[0.22em] text-tosca uppercase">Yang perlu dipahami</p><h2 class="mt-3 text-3xl sm:text-4xl">Batas layanan yang dibuat jelas sejak awal</h2></div>
            <div class="mt-10 space-y-4">
                @foreach ([
                    ['Apakah Starter bisa menggunakan domain sendiri?', 'Belum. Starter memakai subdomain ScriptMedia, misalnya namatoko.scriptmedia.id. Custom domain tersedia mulai Standard.'],
                    ['Apa arti perubahan konten?', 'Perubahan konten adalah bantuan tim ScriptMedia untuk mengganti teks, gambar, banner, atau penyesuaian kecil sesuai kuota paket. Pengelolaan produk dan order tetap dapat dilakukan pemilik melalui Toko Engine.'],
                    ['Apakah desain Pro benar-benar bebas?', 'Pro mencakup kustomisasi desain penuh berdasarkan kebutuhan dan kesepakatan ruang lingkup. Permintaan besar di luar ruang lingkup awal akan ditinjau terlebih dahulu agar jadwal dan hasil tetap terukur.'],
                    ['Kapan toko mulai dibuat?', 'Tim mulai memproses setelah pembayaran terverifikasi. Status pengerjaan dapat dilihat dari detail order di Toko Panel.'],
                    ['Bagaimana pembeli mengecek pesanannya?', 'Pembeli memperoleh link status order yang aman dan tidak membutuhkan akun. Link dapat disimpan atau dikirim melalui WhatsApp.'],
                    ['Apakah data antar-klien bercampur?', 'Tidak. Setiap klien memiliki toko dan database tenant yang terpisah, meskipun menggunakan platform Toko Engine yang sama.'],
                ] as [$question, $answer])
                    <details class="group rounded-card border border-line bg-white p-6 shadow-card">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-navy"><span>{{ $question }}</span><span class="text-xl text-tosca transition group-open:rotate-45">+</span></summary>
                        <p class="mt-4 max-w-4xl leading-7 text-ink-soft">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        <section class="border-t border-line bg-white py-14 print:hidden">
            <div class="mx-auto flex max-w-5xl flex-col items-center px-5 text-center sm:px-8">
                <h2 class="text-3xl sm:text-4xl">Siap melihat paket yang paling sesuai?</h2>
                <p class="mt-4 max-w-2xl leading-7 text-ink-soft">Mulai dari paket yang dibutuhkan sekarang. Kapasitas dan dukungan dapat ditingkatkan ketika bisnis berkembang.</p>
                <div class="mt-7 flex flex-wrap justify-center gap-3"><x-ui.button :href="route('home').'#paket'">Lihat harga paket</x-ui.button>@guest<a href="{{ route('register') }}" class="rounded-full border border-navy px-6 py-3 text-sm font-semibold text-navy hover:bg-offwhite">Daftar sebagai klien</a>@endguest</div>
            </div>
        </section>
    </main>
</x-layouts::public>
