<x-layouts::app title="Pesan toko {{ str($plan->name)->title() }}">
    <div class="mx-auto max-w-4xl space-y-8">
        <x-ui.section-header eyebrow="Pemesanan toko" title="Lengkapi kebutuhan tokomu" description="Data ini akan diperiksa tim ScriptMedia setelah pembayaran berhasil." />

        <div class="grid gap-6 lg:grid-cols-[1fr_300px]">
            <x-ui.card>
                <form method="POST" action="{{ route('onboarding.store', $plan) }}" class="space-y-6">
                    @csrf
                    <div><label for="business_name" class="text-sm font-semibold">Nama bisnis</label><input id="business_name" name="business_name" value="{{ old('business_name') }}" required class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-3 text-navy" placeholder="Toko Senja"></div>
                    <div><label for="desired_subdomain" class="text-sm font-semibold">Subdomain yang diinginkan</label><div class="mt-2 flex rounded-xl border border-line bg-white focus-within:ring-2 focus-within:ring-tosca"><input id="desired_subdomain" name="desired_subdomain" value="{{ old('desired_subdomain') }}" required class="min-w-0 flex-1 rounded-l-xl px-4 py-3 text-navy outline-none" placeholder="tokosenja"><span class="flex items-center border-l border-line px-4 text-sm text-ink-soft">.scriptmedia.id</span></div></div>
                    @if ($plan->custom_domain_allowed)
                        <div><label for="custom_domain" class="text-sm font-semibold">Custom domain <span class="font-normal text-ink-soft">(opsional)</span></label><input id="custom_domain" name="custom_domain" value="{{ old('custom_domain') }}" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-3 text-navy" placeholder="tokosenja.id"></div>
                    @else
                        <div class="rounded-xl bg-tosca-tint px-4 py-3 text-sm text-ink-soft">Plan Starter menggunakan subdomain ScriptMedia. Custom domain tersedia mulai Standard.</div>
                    @endif
                    <div><label for="whatsapp" class="text-sm font-semibold">Nomor WhatsApp</label><input id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}" required class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-3 text-navy" placeholder="+628123456789"></div>
                    <fieldset><legend class="text-sm font-semibold">Periode pembayaran</legend><div class="mt-3 grid gap-3 sm:grid-cols-2"><label class="rounded-xl border border-line bg-white p-4"><input type="radio" name="billing_cycle" value="monthly" @checked(old('billing_cycle', 'monthly') === 'monthly')> <span class="ml-2 font-semibold">Bulanan</span><span class="mt-1 block pl-6 text-sm text-ink-soft">Rp{{ number_format($plan->monthlyTotal(), 0, ',', '.') }}</span></label><label class="rounded-xl border border-line bg-white p-4"><input type="radio" name="billing_cycle" value="annual" @checked(old('billing_cycle') === 'annual')> <span class="ml-2 font-semibold">Tahunan</span><span class="mt-1 block pl-6 text-sm text-ink-soft">Rp{{ number_format($plan->annualTotal(), 0, ',', '.') }} · hemat 2 bulan</span></label></div></fieldset>
                    <div><label for="notes" class="text-sm font-semibold">Catatan kebutuhan <span class="font-normal text-ink-soft">(opsional)</span></label><textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-xl border border-line bg-white px-4 py-3 text-navy" placeholder="Produk yang dijual, warna brand, atau kebutuhan khusus">{{ old('notes') }}</textarea></div>
                    @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><p class="font-semibold">Periksa kembali data berikut:</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    <x-ui.button type="submit" class="w-full">Lanjut ke pembayaran</x-ui.button>
                </form>
            </x-ui.card>
            <x-ui.card class="h-fit lg:sticky lg:top-24">
                <x-ui.badge variant="navy">{{ str($plan->name)->title() }}</x-ui.badge><p class="mt-4 text-3xl">Rp{{ number_format($plan->monthlyTotal(), 0, ',', '.') }}<span class="text-sm text-ink-soft"> / bulan</span></p><dl class="mt-5 space-y-3 border-t border-line pt-5 text-sm"><div class="flex justify-between"><dt class="text-ink-soft">Platform</dt><dd>Rp{{ number_format((float) $plan->price_platform, 0, ',', '.') }}</dd></div><div class="flex justify-between"><dt class="text-ink-soft">Web Care</dt><dd>Rp{{ number_format((float) $plan->price_care_monthly, 0, ',', '.') }}</dd></div></dl>
            </x-ui.card>
        </div>
    </div>
</x-layouts::app>
