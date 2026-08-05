<x-layouts::auth :title="__('Masuk')">
    <div class="auth-form mx-auto w-full max-w-md">
        <div>
            <span class="text-xs font-semibold tracking-[0.16em] text-tosca uppercase">Selamat datang kembali</span>
            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.035em] text-navy sm:text-4xl">Masuk ke dashboard toko</h2>
            <p class="mt-3 max-w-sm text-sm leading-6 text-ink-soft">Gunakan akun pengelola untuk mengatur produk, pesanan, dan operasional tokomu.</p>
        </div>

        <x-auth-session-status class="mt-6 rounded-xl border border-tosca/20 bg-tosca-tint p-4 text-sm text-navy" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                label="Alamat email"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="nama@tokomu.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    label="Kata sandi"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm font-semibold text-tosca hover:text-navy end-0" :href="route('password.request')" wire:navigate>
                        Lupa kata sandi?
                    </flux:link>
                @endif
            </div>

            <div class="flex items-center justify-between gap-4">
                <flux:checkbox name="remember" label="Ingat saya" :checked="old('remember')" />
                <span class="inline-flex items-center gap-2 text-xs text-ink-soft"><span class="size-1.5 rounded-full bg-tosca"></span>Akses aman</span>
            </div>

            <button type="submit" class="mt-1 inline-flex w-full items-center justify-center gap-2 rounded-full bg-orange px-5 py-3.5 text-sm font-semibold text-navy shadow-[0_10px_24px_rgba(244,163,0,0.24)] transition hover:-translate-y-0.5 hover:bg-orange-light focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange" data-test="login-button">
                Masuk ke dashboard
                <span aria-hidden="true">&rarr;</span>
            </button>
        </form>

        <!-- <x-passkey-verify /> -->

        <p class="mt-7 text-center text-xs leading-5 text-ink-soft/75">Dengan masuk, kamu menyetujui kebijakan keamanan dan penggunaan sistem Toko Engine.</p>
    </div>
</x-layouts::auth>
