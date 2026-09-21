# Repository Guidelines

Dokumen ini bersifat informatif: menjelaskan bagaimana `toko-engine` disusun hari ini, supaya siapa pun (manusia maupun AI) bisa cepat paham konteksnya. Arahan terbaru dari pemilik project selalu jadi acuan utama.

## Project Scope & Architecture

`toko-engine` adalah aplikasi e-commerce ScriptMedia yang dipakai ulang untuk katalog, keranjang, checkout, order, dan administrasi toko.

Rencananya aplikasi ini punya dua mode runtime:

- **Hosted** (`TENANCY_ENABLED=true`): melayani seluruh domain toko publik, memilih database tenant lewat `stancl/tenancy` berdasarkan domain.
- **Standalone** (`TENANCY_ENABLED=false`): satu database biasa, cocok untuk template yang dijual lepas.

Orkestrasi provisioning (membuat database, menjalankan migrasi tenant, mendaftarkan domain) berada di `toko-panel`.

Batasan paket diakses lewat `StoreLimitService` agar terpusat. Service ini bisa membaca limit produk / payment gateway dari central database panel, dan punya fallback ke config lokal atau unlimited untuk instalasi standalone.

## Demo Stores (tahap saat ini)

Tiga demo storefront hidup di `config/demo-stores.php` dan dilayani dari prefix `/starter`, `/standard`, `/pro` melalui middleware `demo.store` + helper `App\Support\StorefrontContext`. Demo ini adalah materi jualan untuk klien; sistem plan enforcement penuh menyusul setelah demo disetujui.

## Project Structure & Module Organization

Kelas aplikasi ada di `app/`; komponen Livewire berbasis kelas di `app/Livewire/` dengan template di `resources/views/livewire/`. Route HTTP dan settings ada di `routes/`. Migrasi, factory, dan seeder di `database/`. Sumber frontend di `resources/css/` dan `resources/js/`; aset statis yang disajikan publik di `public/`. Test terbagi antara `tests/Feature/` dan `tests/Unit/`.

## Build, Test, and Development Commands

- `composer setup` — install dependency PHP/Node, siapkan `.env`, migrate, build aset.
- `composer dev` — jalankan Laravel, queue listener, dan Vite bersamaan.
- `composer test` — Pint check, PHPStan level 7, lalu seluruh test suite.
- `composer lint` — perbaiki format PHP; `composer types:check` untuk analisis statis.
- `npm run build` — build aset frontend produksi.

## Coding Style & Naming Conventions

Ikuti `.editorconfig`: UTF-8, LF, indentasi empat spasi (dua untuk YAML), dan newline di akhir file. Gunakan preset `laravel` dari Laravel Pint dan namespace PSR-4. Kelas PascalCase, method/variabel camelCase, kolom database snake_case, file Blade kebab-case. Komponen Livewire ditulis berbasis kelas (bukan Volt) agar konsisten dengan yang sudah ada.

## Testing Guidelines

Gunakan Pest 5/PHPUnit dan `RefreshDatabase` untuk perilaku database. Beri nama test sesuai fitur dan perilakunya, misalnya `tests/Feature/Checkout/PlaceOrderTest.php`. Cakup happy path, validasi, otorisasi, kegagalan, dan regresi tiap bug. CI memakai PHP 8.3 dan Node 22 serta menjalankan `composer ci:check`.

## Design

Acuan visual ada di `docs/references/sewa-toko-online.html`: font Questrial, navy `#0B2545`, tosca `#2CA6A4`, orange `#F4A300`, off-white `#F4FAFA`, kartu radius 18px, border tipis, pill badge solid, dan hero gradient navy.

## Security

`.env`, kredensial, data pelanggan, dan database hasil generate sebaiknya tetap di luar git. Kredensial Midtrans sandbox dan produksi adalah pasangan key yang berbeda di dashboard Midtrans, dan `MIDTRANS_IS_PRODUCTION` menentukan endpoint Snap yang dipakai — pastikan key di `.env` berasal dari environment yang sama dengan flag tersebut.

## Contributions

Gunakan pesan commit imperatif yang ringkas, misalnya `Add cart quantity validation`. Pull request sebaiknya menjelaskan scope, perubahan migrasi/config, dan cara verifikasinya, menautkan issue terkait, serta menyertakan screenshot untuk pekerjaan UI.

## Catatan Produk & Arsitektur

Catatan berikut merangkum keputusan yang berlaku saat ini. Kalau ada arahan baru dari pemilik project, arahan itu yang dipakai.

### Domain Serving & Tenancy

- `toko-engine` adalah aplikasi yang melayani trafik storefront publik. Wildcard subdomain `*.scriptmedia.id` dan custom domain klien diarahkan ke deployment `toko-engine`.
- `toko-panel` berjalan di satu domain central terpisah, misalnya `panel.scriptmedia.id`, untuk billing dan tiket.
- `stancl/tenancy` ditempatkan di `toko-engine`. Saat `TENANCY_ENABLED=true`, `InitializeTenancyByDomain` memetakan domain ke record tenant lewat read connection ke central database, lalu mengganti default connection ke database tenant tersebut.
- Tenant hosted berbagi codebase `toko-engine`, tetapi tiap klien punya database sendiri. Satu klien = satu tenant = satu toko = satu database tenant; server fisik terpisah per klien tidak diperlukan.
- Saat `TENANCY_ENABLED=false`, tenancy dilewati dan aplikasi memakai database default.
- Provisioning ditangani `toko-panel`: membuat database dan record central, menjalankan migrasi `toko-engine` ke database tenant baru, mendaftarkan domain, dan membuat akun owner tenant.
- DNS mengarahkan wildcard `*.scriptmedia.id` dan CNAME custom domain klien ke `toko-engine`; domain panel mengarah ke `toko-panel`. Keduanya boleh berbagi server fisik dengan virtual host terpisah.

### Accounts, Plans & Feature Rules

- Pemilik toko punya dua akun terpisah secara sengaja: akun di central DB untuk portal billing/tiket panel, dan user record di DB tenant untuk area administrasi toko. Provisioning membuat keduanya dari identitas email/kontak yang sama. SSO atau token lintas aplikasi belum termasuk scope saat ini.
- Limit toko bersumber dari record `plans` central melalui `StoreLimitService`, dengan fallback config lokal / unlimited untuk mode standalone.
- Midtrans memakai satu merchant account bersama. Starter mengirim `enabled_payments=['other_qris']`; Standard menambahkan kanal VA (`bca_va`, `bni_va`, `bri_va`, `permata_va`, dan sejenisnya); Pro membiarkan seluruh kanal aktif, termasuk kartu kredit dan e-wallet.
- Permintaan ubah konten dihitung final: jumlah `content_change_requests` dalam rentang `current_period_start` sampai `current_period_end` subscription aktif, dibandingkan dengan `plans.content_request_quota`.
- Harga tahunan dihitung dinamis sebagai `(price_platform + price_care_monthly) * 10` dengan masa aktif 12 bulan, sehingga kolom harga tahunan terpisah tidak diperlukan.
- Nilai paket bulanan kanonis: Starter `150000 + 150000`, Standard `150000 + 350000`, Pro `150000 + 550000` rupiah untuk platform + care.
- Alur bisnis penjualan source code template ditunda. Kemampuan standalone tetap dipertahankan; model lisensi, one-time-sale, setup service, dan care plan opsional menyusul kalau diminta.
