<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Buat akun klien" description="Daftar untuk memilih paket dan memantau proses pembuatan toko." />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <input type="hidden" name="plan" value="{{ request('plan') }}">
            <!-- Name -->
            <flux:input
                name="name"
                label="Nama lengkap"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Nama lengkap"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                label="Alamat email"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                label="Password"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Minimal 8 karakter"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                label="Ulangi password"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Ulangi password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <x-ui.button type="submit" class="w-full" data-test="register-user-button">Buat akun</x-ui.button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>Sudah punya akun?</span>
            <flux:link :href="route('login')" wire:navigate>Masuk</flux:link>
        </div>
    </div>
</x-layouts::auth>
