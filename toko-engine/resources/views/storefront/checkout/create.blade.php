<x-layouts::storefront title="Checkout">
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-18">
        <x-ui.badge variant="orange">Checkout</x-ui.badge>
        <h1 class="mt-4 text-4xl text-navy sm:text-5xl">Lengkapi pesanan</h1>
        <p class="mt-3 max-w-2xl text-ink-soft">Pastikan data penerima dan metode pembayaran sudah sesuai sebelum membuat order.</p>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
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
                        @forelse ($gateways as $gateway)
                            <label class="flex cursor-pointer gap-3 rounded-xl border border-line p-4 transition hover:border-tosca/50 hover:bg-tosca-tint/40">
                                <input type="radio" name="payment_gateway_code" value="{{ $gateway->code }}" @checked(old('payment_gateway_code', $gateways->first()?->code) === $gateway->code)>
                                <span><span class="block font-semibold text-navy">{{ $gateway->name }}</span><span class="mt-1 block text-sm leading-6 text-ink-soft">{{ $gateway->instructions }}</span></span>
                            </label>
                        @empty
                            <x-ui.empty-state compact icon="payment" title="Metode pembayaran belum tersedia" description="Pengelola toko belum mengaktifkan payment gateway. Silakan coba kembali nanti." />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card class="h-fit lg:sticky lg:top-24">
                <h2 class="text-xl text-navy">Ringkasan order</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($items as $item)
                        <div class="flex justify-between gap-3 text-sm"><span class="text-ink-soft">{{ $item['product']->name }} &times; {{ $item['quantity'] }}</span><span class="font-semibold text-navy">Rp{{ number_format($item['line_total'], 0, ',', '.') }}</span></div>
                    @endforeach
                </div>
                <div class="mt-5 flex justify-between border-t border-line pt-5"><span>Total</span><span class="text-lg font-semibold text-navy">Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
                <div class="mt-6"><x-ui.loading-button loading-label="Mencatat order..." class="w-full" :disabled="$gateways->isEmpty()">Buat order</x-ui.loading-button></div>
            </x-ui.card>
        </form>
    </section>
</x-layouts::storefront>
