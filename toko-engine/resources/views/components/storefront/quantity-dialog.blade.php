{{--
    One shared dialog for the whole storefront. Product cards and the detail page
    open it with an `open-quantity-dialog` window event instead of each rendering
    their own copy.
--}}
<div
    x-data="{
        open: false,
        intent: 'cart',
        product: { name: '', price: 0, stock: 1, icon: '', color: '#e3f4f3', cartUrl: '', buyUrl: '', variantId: null },
        quantity: 1,
        show(detail) {
            this.product = detail.product
            this.intent = detail.intent
            this.quantity = 1
            this.open = true
            this.$nextTick(() => this.$refs.quantityInput?.focus())
        },
        step(by) {
            this.quantity = Math.min(Math.max(this.quantity + by, 1), this.product.stock)
        },
        normalise() {
            const value = Number.parseInt(this.quantity, 10)
            this.quantity = Number.isNaN(value) ? 1 : Math.min(Math.max(value, 1), this.product.stock)
        },
        get total() { return this.product.price * this.quantity },
        format(value) { return 'Rp' + Math.round(value).toLocaleString('id-ID') },
    }"
    x-on:open-quantity-dialog.window="show($event.detail)"
    x-on:keydown.escape.window="open = false"
>
    <div x-show="open" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center bg-navy/45 p-0 backdrop-blur-sm sm:items-center sm:p-5" x-on:click.self="open = false" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="quantity-dialog-title">
        <div
            class="w-full max-w-md rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-card"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        >
            <div class="flex items-start gap-4">
                <div class="flex size-16 shrink-0 items-center justify-center rounded-xl text-3xl" x-bind:style="'background-color: ' + product.color" x-text="product.icon"></div>
                <div class="min-w-0 flex-1">
                    <h2 id="quantity-dialog-title" class="text-lg leading-snug font-semibold text-navy" x-text="product.name"></h2>
                    <p class="mt-1 text-sm text-ink-soft"><span x-text="format(product.price)"></span> / item · stok <span x-text="product.stock"></span></p>
                </div>
                <button type="button" class="-mt-1 cursor-pointer text-xl leading-none text-ink-soft hover:text-navy" x-on:click="open = false" aria-label="Tutup">&times;</button>
            </div>

            <div class="mt-6 flex items-center justify-between gap-4 rounded-xl bg-offwhite px-4 py-3">
                <span class="text-sm font-semibold text-navy">Jumlah</span>
                <div class="flex items-center gap-2">
                    <button type="button" class="flex size-9 cursor-pointer items-center justify-center rounded-full border border-line bg-white text-lg text-navy disabled:opacity-40" x-on:click="step(-1)" x-bind:disabled="quantity <= 1" aria-label="Kurangi jumlah">&minus;</button>
                    <input x-ref="quantityInput" type="number" min="1" x-bind:max="product.stock" x-model="quantity" x-on:change="normalise()" x-on:blur="normalise()" class="w-16 rounded-xl border border-line bg-white px-2 py-2 text-center text-navy" aria-label="Jumlah produk">
                    <button type="button" class="flex size-9 cursor-pointer items-center justify-center rounded-full border border-line bg-white text-lg text-navy disabled:opacity-40" x-on:click="step(1)" x-bind:disabled="quantity >= product.stock" aria-label="Tambah jumlah">+</button>
                </div>
            </div>

            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-sm text-ink-soft">Total</span>
                <span class="text-xl font-semibold text-navy" x-text="format(total)"></span>
            </div>

            <form method="POST" x-bind:action="intent === 'buy' ? product.buyUrl : product.cartUrl" class="mt-6">
                @csrf
                <input type="hidden" name="quantity" x-bind:value="quantity">
                <template x-if="product.variantId"><input type="hidden" name="variant_id" x-bind:value="product.variantId"></template>
                <x-ui.loading-button loading-label="Memproses..." variant="navy" class="w-full cursor-pointer">
                    <span x-text="intent === 'buy' ? 'Beli sekarang' : 'Masukkan ke keranjang'"></span>
                </x-ui.loading-button>
            </form>
            <button type="button" class="mt-3 w-full cursor-pointer py-2 text-sm font-semibold text-ink-soft hover:text-navy" x-on:click="open = false">Batal</button>
        </div>
    </div>
</div>
