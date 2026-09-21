# Repository Guidelines

Dokumen ini bersifat informatif: menjelaskan bagaimana `toko-panel` disusun hari ini, supaya siapa pun (manusia maupun AI) bisa cepat paham konteksnya. Arahan terbaru dari pemilik project selalu jadi acuan utama.

## Project Scope & Architecture

`toko-panel` adalah control panel central ScriptMedia untuk tenant, plan, subscription, invoice, tiket ubah konten, dan provisioning toko. Panel berjalan di domain central-nya sendiri untuk admin ScriptMedia dan pemilik tenant; storefront tenant dilayani oleh `toko-engine`.

Platform memakai satu central database untuk plan, tenant, subscription, domain, dan billing, ditambah database terpisah per klien. Database tenant mengikuti struktur migrasi `toko-engine`. Resolusi domain runtime dan perpindahan database lewat `stancl/tenancy` berada di `toko-engine`; panel berperan sebagai orkestrator provisioning. Limit paket (produk, gateway, kuota dukungan, dan aturan sejenis) bersumber dari record plan, bukan nilai hardcode.

## Project Structure & Module Organization

Kode aplikasi Laravel ada di `app/`; komponen Livewire berbasis kelas di `app/Livewire/` dengan Blade view di `resources/views/livewire/`. Route terbagi antara `routes/web.php`, `routes/settings.php`, dan route console. Migrasi, factory, dan seeder di `database/`. Sumber frontend di `resources/css/` dan `resources/js/`, file statis publik di `public/`. Test Pest ditaruh di `tests/Feature/` atau `tests/Unit/`.

## Build, Test, and Development Commands

- `composer setup` — install dependency, buat `.env`, generate key, migrate, build aset.
- `composer dev` — jalankan server Laravel, queue listener, dan Vite bersamaan.
- `composer test` — bersihkan config, cek Pint, jalankan PHPStan level 7, lalu Pest.
- `composer lint` — perbaiki format PHP; `composer types:check` untuk analisis statis saja.
- `npm run build` — build aset frontend produksi.

## Coding Style & Naming Conventions

Ikuti `.editorconfig`: UTF-8, LF, indentasi empat spasi (dua untuk YAML), dan newline di akhir file. Gunakan preset `laravel` dari Laravel Pint dan namespace PSR-4. Kelas PascalCase, method/variabel camelCase, kolom database snake_case, file Blade kebab-case. Komponen Livewire ditulis berbasis kelas (bukan Volt), dipin lewat `composer.lock`.

## Testing Guidelines

Gunakan Pest 5. Beri nama test sesuai perilaku dan letakkan feature test dekat domain yang terpengaruh, misalnya `tests/Feature/Billing/InvoiceTest.php`. Tambahkan regresi untuk tiap perbaikan bug dan test untuk batas central-versus-tenant database. Belum ada ambang coverage; prioritaskan jalur sukses, validasi, otorisasi, dan kegagalan yang bermakna.

## Design

Acuan visual ada di `docs/references/sewa-toko-online.html`: font Questrial, navy `#0B2545`, tosca `#2CA6A4`, orange `#F4A300`, off-white `#F4FAFA`, kartu radius 18px, border tipis, pill badge, dan hero gradient navy.

## Security

`.env`, kredensial, data tenant, dan database hasil generate sebaiknya tetap di luar git. Kredensial Midtrans sandbox dan produksi adalah pasangan key yang berbeda di dashboard Midtrans, dan `MIDTRANS_IS_PRODUCTION` menentukan endpoint Snap yang dipakai — pastikan key di `.env` berasal dari environment yang sama dengan flag tersebut. 

## Contributions

Gunakan pesan commit imperatif yang ringkas, misalnya `Add tenant invoice status filter`. Pull request sebaiknya menjelaskan scope, migrasi, dampak tenancy, dan cara verifikasinya; tautkan issue dan sertakan screenshot untuk perubahan UI.

## Catatan Produk & Arsitektur

Catatan berikut merangkum keputusan yang berlaku saat ini. Kalau ada arahan baru dari pemilik project, arahan itu yang dipakai.

### Domain Serving & Tenancy

- `toko-engine` melayani trafik storefront publik. Wildcard subdomain `*.scriptmedia.id` dan custom domain klien diarahkan ke deployment `toko-engine`.
- `toko-panel` berjalan di satu domain central terpisah, misalnya `panel.scriptmedia.id`, dipakai admin ScriptMedia dan owner untuk billing serta tiket.
- `stancl/tenancy` dan `InitializeTenancyByDomain` ditempatkan di `toko-engine`. Route dan middleware tenant-domain di sisi panel tidak diperlukan.
- Tenant hosted berbagi codebase `toko-engine`, tetapi tiap klien punya database sendiri. Satu klien = satu tenant = satu toko = satu database tenant; server fisik terpisah per klien tidak diperlukan.
- Tanggung jawab provisioning panel: membuat record central dan database tenant, menjalankan migrasi `toko-engine` ke database itu, mendaftarkan subdomain/custom domain, dan membuat akun owner tenant.
- DNS mengarahkan wildcard `*.scriptmedia.id` dan CNAME custom domain klien ke `toko-engine`; domain panel mengarah ke `toko-panel`. Keduanya boleh berbagi server fisik dengan virtual host terpisah.
- `toko-engine` dapat dijalankan standalone dengan `TENANCY_ENABLED=false` untuk instalasi template self-hosted; permintaan standalone berada di luar kendali panel.

### Accounts, Billing & Feature Rules

- Pemilik toko punya dua akun terpisah secara sengaja: akun di central DB untuk portal billing/tiket panel ini, dan user record di DB tenant untuk area administrasi toko. Provisioning membuat keduanya dari identitas email/kontak yang sama. SSO atau token lintas aplikasi belum termasuk scope saat ini.
- Midtrans memakai satu merchant account bersama. Starter memakai `other_qris`; Standard menambahkan kanal VA (`bca_va`, `bni_va`, `bri_va`, `permata_va`, dan sejenisnya); Pro membiarkan seluruh kanal aktif, termasuk kartu kredit dan e-wallet.
- Perhitungan permintaan ubah konten: hitung `content_change_requests` yang pemakaiannya jatuh dalam rentang inklusif `current_period_start` sampai `current_period_end` subscription aktif, lalu bandingkan dengan `plans.content_request_quota`.
- Harga paket dijaga sederhana: invoice tahunan dihasilkan dinamis sebagai `(price_platform + price_care_monthly) * 10` dengan periode subscription 12 bulan, sehingga kolom `price_care_annual` / `price_platform_annual` tidak diperlukan.
- Nilai seed kanonis: Starter `price_platform=150000`, `price_care_monthly=150000`; Standard `price_platform=150000`, `price_care_monthly=350000`; Pro `price_platform=150000`, `price_care_monthly=550000`.
- Alur bisnis penjualan source code template ditunda. Model lisensi, one-time-sale, setup service, dan care plan opsional menyusul kalau diminta.
