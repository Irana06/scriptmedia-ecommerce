<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-12">
        <section class="relative overflow-hidden rounded-card bg-linear-to-br from-navy via-navy-mid to-navy px-6 py-10 text-white shadow-xl shadow-navy/15 sm:px-10 sm:py-14">
            <div class="absolute -top-20 -right-16 size-64 rounded-full bg-orange/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-1/3 size-56 rounded-full bg-tosca/20 blur-3xl"></div>

            <div class="relative max-w-3xl">
                <x-ui.badge variant="orange">Control panel internal</x-ui.badge>
                <h1 class="mt-5 text-4xl leading-tight sm:text-5xl">Kelola operasional toko dalam satu tempat.</h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-white/75 sm:text-lg">
                    Pantau tenant, subscription, billing, dan request konten melalui alur kerja yang konsisten untuk tim ScriptMedia.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-ui.button :href="route('profile.edit')" wire:navigate>Pengaturan akun</x-ui.button>
                    <x-ui.button href="#ringkasan" variant="navy" class="ring-1 ring-white/30 hover:ring-white/50">
                        Lihat ringkasan
                    </x-ui.button>
                </div>
            </div>
        </section>

        <section id="ringkasan">
            <x-ui.section-header
                eyebrow="Ringkasan platform"
                title="Fondasi untuk operasional multi-tenant"
                description="Komponen dashboard ini menjadi contoh penggunaan token warna, card, badge, dan tombol yang reusable."
            />

            <div class="mt-8 grid gap-5 md:grid-cols-3">
                <x-ui.card>
                    <div class="flex items-start justify-between gap-4">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-tosca-tint text-tosca">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 20h16M6 20V8l6-4 6 4v12M9 20v-5h6v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <x-ui.badge>Tenant</x-ui.badge>
                    </div>
                    <h3 class="mt-6 text-xl text-navy">Manajemen tenant</h3>
                    <p class="mt-2 leading-6 text-ink-soft">Kelola identitas klien dan status provisioning dari data central.</p>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-start justify-between gap-4">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-orange/15 text-orange">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 7h14v12H5zM8 4h8v3M8 11h8M8 15h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <x-ui.badge variant="orange">Billing</x-ui.badge>
                    </div>
                    <h3 class="mt-6 text-xl text-navy">Subscription & invoice</h3>
                    <p class="mt-2 leading-6 text-ink-soft">Satukan siklus plan, subscription, dan invoice dalam alur yang mudah diaudit.</p>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-start justify-between gap-4">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-navy/10 text-navy">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 6h16v10H8l-4 4V6zM8 10h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <x-ui.badge variant="navy">Support</x-ui.badge>
                    </div>
                    <h3 class="mt-6 text-xl text-navy">Request konten</h3>
                    <p class="mt-2 leading-6 text-ink-soft">Tindak lanjuti tiket perubahan konten berdasarkan benefit plan yang tersimpan.</p>
                </x-ui.card>
            </div>
        </section>
    </div>
</x-layouts::app>
