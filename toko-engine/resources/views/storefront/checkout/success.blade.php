<x-layouts::storefront title="Order berhasil">
    <section class="mx-auto max-w-3xl px-5 py-16 sm:px-8 sm:py-24">
        <x-ui.card class="text-center">
            <span class="mx-auto flex size-16 items-center justify-center rounded-full bg-tosca-tint text-tosca"><svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></span>
            <x-ui.badge variant="orange" class="mt-6">Order tercatat</x-ui.badge>
            <h1 class="mt-4 text-4xl text-navy">Terima kasih!</h1>
            <p class="mt-3 text-ink-soft">Nomor order <strong class="text-navy">{{ $order->number }}</strong></p>

            <div class="mt-8 rounded-xl bg-offwhite p-5 text-left">
                <div class="flex justify-between"><span class="text-ink-soft">Total</span><span class="font-semibold text-navy">Rp{{ number_format((float) $order->total, 0, ',', '.') }}</span></div>
                <div class="mt-3 flex justify-between"><span class="text-ink-soft">Status order</span><x-ui.badge>{{ ucfirst($order->status) }}</x-ui.badge></div>
                <div class="mt-3 flex justify-between"><span class="text-ink-soft">Pembayaran</span><x-ui.badge variant="{{ $order->payment_status === 'paid' ? 'tosca' : ($order->payment_status === 'failed' ? 'navy' : 'orange') }}">{{ ucfirst($order->payment_status) }}</x-ui.badge></div>
            </div>

            @if ($gateway?->code === \App\Services\MidtransService::GATEWAY_CODE)
                <div class="mt-6 rounded-xl border border-tosca/25 bg-tosca-tint/55 p-5 text-left">
                    <div class="flex items-start justify-between gap-4"><div><p class="font-semibold text-navy">Pembayaran Midtrans</p><p class="mt-2 text-sm leading-6 text-ink-soft">Status pembayaran hanya diperbarui setelah notifikasi aman dari Midtrans diterima.</p></div>@unless (config('services.midtrans.is_production'))<x-ui.badge variant="navy">Sandbox</x-ui.badge>@endunless</div>

                    @if ($order->payment_status === 'paid')
                        <p class="mt-5 text-sm font-semibold text-tosca">Pembayaran telah terverifikasi.</p>
                    @elseif ($order->payment_checkout_token && $midtransClientKey && $midtransSnapJsUrl)
                        <button id="midtrans-pay-button" type="button" class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-orange px-5 py-3 text-sm font-semibold text-navy transition hover:bg-orange-light cursor-pointer">Bayar sekarang dengan Midtrans</button>
                        <p id="midtrans-message" class="mt-3 text-xs leading-5 text-ink-soft">Popup pembayaran aman akan dibuka oleh Midtrans Snap.</p>
                    @elseif ($midtransRetryUrl)
                        <form method="POST" action="{{ $midtransRetryUrl }}" class="mt-5">@csrf<x-ui.loading-button loading-label="Menghubungi Midtrans..." class="w-full">Coba siapkan pembayaran</x-ui.loading-button></form>
                    @endif
                </div>
            @elseif ($gateway)
                <div class="mt-6 rounded-xl border border-orange/30 bg-orange/10 p-5 text-left"><p class="font-semibold text-navy">Instruksi {{ $gateway->name }}</p><p class="mt-2 text-sm leading-6 text-ink-soft">{{ $gateway->instructions }}</p></div>
            @endif

            <div class="mt-6 rounded-xl border border-line bg-white p-5 text-left">
                <p class="font-semibold text-navy">Simpan link status order</p>
                <p class="mt-2 text-sm leading-6 text-ink-soft">Link pribadi ini tetap dapat dibuka untuk mengecek pembayaran dan proses pesanan tanpa membuat akun.</p>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                    <x-ui.button :href="$trackingUrl" variant="navy">Lihat status order</x-ui.button>
                    <a href="{{ $whatsappTrackingUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-full border border-tosca px-5 py-3 text-sm font-semibold text-tosca transition hover:bg-tosca-tint">Kirim link ke WhatsApp</a>
                </div>
            </div>

            <div class="mt-8"><x-ui.button :href="route('products.index')" variant="navy">Lanjut belanja</x-ui.button></div>
        </x-ui.card>
    </section>

    @if ($gateway?->code === \App\Services\MidtransService::GATEWAY_CODE && $order->payment_checkout_token && $midtransClientKey && $midtransSnapJsUrl)
        <script src="{{ $midtransSnapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
        <script>
            document.getElementById('midtrans-pay-button')?.addEventListener('click', function () {
                const message = document.getElementById('midtrans-message');

                window.snap.pay(@js($order->payment_checkout_token), {
                    onSuccess: function () { window.location.reload(); },
                    onPending: function () { window.location.reload(); },
                    onError: function () { message.textContent = 'Pembayaran gagal diproses. Silakan coba lagi.'; },
                    onClose: function () { message.textContent = 'Pembayaran belum diselesaikan. Kamu dapat membuka Midtrans kembali.'; },
                });
            });
        </script>
    @endif
</x-layouts::storefront>
