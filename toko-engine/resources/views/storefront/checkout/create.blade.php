@php($singleGateway = $gateways->count() === 1 ? $gateways->first() : null)

<x-layouts::storefront title="Pembayaran">
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-18">
        <x-ui.badge variant="orange">Pembayaran</x-ui.badge>
        <h1 class="mt-4 text-4xl text-navy sm:text-5xl">Lengkapi pesanan</h1>
        <p class="mt-3 max-w-2xl text-ink-soft">Pastikan data penerima dan metode pembayaran sudah sesuai sebelum membuat pesanan.</p>

        <form method="POST" action="{{ \App\Support\StorefrontContext::route('checkout.store') }}" class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
            @csrf
            <div class="space-y-6">
                <x-ui.card>
                    <h2 class="text-xl text-navy">Data penerima</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-semibold text-navy">Nama<input name="customer_name" value="{{ old('customer_name') }}" required class="rounded-xl border border-line px-4 py-3 font-normal"></label>
                        <label class="grid gap-2 text-sm font-semibold text-navy">Email<input type="email" name="customer_email" value="{{ old('customer_email') }}" required class="rounded-xl border border-line px-4 py-3 font-normal"></label>
                        <label class="grid gap-2 text-sm font-semibold text-navy">Telepon<input name="customer_phone" value="{{ old('customer_phone') }}" required class="rounded-xl border border-line px-4 py-3 font-normal"></label>
                        <label class="grid gap-2 text-sm font-semibold text-navy sm:col-span-2">Alamat<textarea name="shipping_address" rows="4" required class="rounded-xl border border-line px-4 py-3 font-normal">{{ old('shipping_address') }}</textarea></label>
                        <label class="grid gap-2 text-sm font-semibold text-navy sm:col-span-2">Catatan opsional<textarea name="notes" rows="3" class="rounded-xl border border-line px-4 py-3 font-normal">{{ old('notes') }}</textarea></label>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-xl text-navy">Metode pembayaran</h2>
                    <div class="mt-5 space-y-3">
                        @if ($singleGateway)
                            {{-- One gateway means there is nothing to choose, so this reads as information. --}}
                            <input type="hidden" name="payment_gateway_code" value="{{ $singleGateway->code }}">
                            @php($isMidtrans = $singleGateway->code === \App\Services\MidtransService::GATEWAY_CODE)
                            <div class="rounded-xl border border-line p-4">
                                <p class="font-semibold text-navy">{{ $singleGateway->name }}</p>
                                <p class="mt-1 text-sm leading-6 text-ink-soft">{{ $isMidtrans ? $midtransPaymentDescription : $singleGateway->instructions }}</p>
                                @if ($isMidtrans && $midtransChannelGroups !== [])
                                    <div class="mt-3 grid gap-2">
                                        @foreach ($midtransChannelGroups as $channelGroup)
                                            <div class="flex gap-2.5 rounded-xl bg-offwhite px-3 py-2.5">
                                                <svg class="mt-0.5 size-4 shrink-0 text-tosca" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                <div><p class="text-sm font-semibold text-navy">{{ $channelGroup['label'] }}</p><p class="mt-0.5 text-xs leading-5 text-ink-soft">{{ $channelGroup['detail'] }}</p></div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="mt-3 text-xs leading-5 text-ink-soft">Metode dipilih pada halaman pembayaran setelah pesanan dibuat.</p>
                                @endif
                            </div>
                        @else
                            @forelse ($gateways as $gateway)
                                @php($isMidtrans = $gateway->code === \App\Services\MidtransService::GATEWAY_CODE)
                                <label class="flex cursor-pointer gap-3 rounded-xl border border-line p-4 transition hover:border-tosca/50 hover:bg-tosca-tint/40">
                                    <input type="radio" name="payment_gateway_code" value="{{ $gateway->code }}" @checked(old('payment_gateway_code', $gateways->first()?->code) === $gateway->code) class="mt-1.5 shrink-0 accent-tosca">
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-navy">{{ $gateway->name }}</span>
                                        <span class="mt-1 block text-sm leading-6 text-ink-soft">{{ $isMidtrans ? $midtransPaymentDescription : $gateway->instructions }}</span>
                                        @if ($isMidtrans && $midtransChannelGroups !== [])
                                            <span class="mt-3 grid gap-2">
                                                @foreach ($midtransChannelGroups as $channelGroup)
                                                    <span class="flex gap-2.5 rounded-xl bg-offwhite px-3 py-2.5">
                                                        <svg class="mt-0.5 size-4 shrink-0 text-tosca" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        <span><span class="block text-sm font-semibold text-navy">{{ $channelGroup['label'] }}</span><span class="mt-0.5 block text-xs leading-5 text-ink-soft">{{ $channelGroup['detail'] }}</span></span>
                                                    </span>
                                                @endforeach
                                            </span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <x-ui.empty-state compact icon="payment" title="Metode pembayaran belum tersedia" description="Pengelola toko belum mengaktifkan metode pembayaran. Silakan coba kembali nanti." />
                            @endforelse
                        @endif
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card class="h-fit lg:sticky lg:top-24">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="text-xl text-navy">Produk yang dipesan</h2>
                    <a href="{{ \App\Support\StorefrontContext::route('cart.index') }}" class="text-xs font-semibold text-tosca">Ubah</a>
                </div>
                <p class="mt-1 text-xs text-ink-soft">{{ $items->count() }} produk dari keranjang.</p>
                <div class="mt-5 space-y-3">
                    @foreach ($items as $item)
                        <div class="flex justify-between gap-3 text-sm"><span class="text-ink-soft">{{ $item->label() }} &times; {{ $item->quantity }}</span><span class="font-semibold text-navy">Rp{{ number_format($item->lineTotal(), 0, ',', '.') }}</span></div>
                    @endforeach
                </div>
                <div class="mt-5 flex justify-between border-t border-line pt-5"><span>Total</span><span class="text-lg font-semibold text-navy">Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
                <div class="mt-6"><x-ui.loading-button loading-label="Menyimpan pesanan..." class="w-full cursor-pointer" :disabled="$gateways->isEmpty()">Buat pesanan</x-ui.loading-button></div>
            </x-ui.card>
        </form>
    </section>
</x-layouts::storefront>
