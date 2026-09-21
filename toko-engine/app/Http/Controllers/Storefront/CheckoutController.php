<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\StoreLimitService;
use App\Support\CartLine;
use App\Support\OrderTimeline;
use App\Support\StorefrontContext;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function create(CartService $cart, StoreLimitService $storeLimits): View|RedirectResponse
    {
        if ($cart->selectedItems()->isEmpty()) {
            return redirect(StorefrontContext::route('cart.index'))
                ->withErrors(['cart' => $cart->items()->isEmpty()
                    ? 'Keranjang masih kosong.'
                    : 'Pilih dulu produk yang mau dipesan.']);
        }

        return view('storefront.checkout.create', [
            'items' => $cart->selectedItems(),
            'subtotal' => $cart->selectedSubtotal(),
            'gateways' => $this->availableGateways(),
            'midtransPaymentDescription' => $storeLimits->paymentMethodDescription(),
            'midtransChannelGroups' => $storeLimits->midtransChannelGroups(),
        ]);
    }

    public function store(Request $request, CartService $cart, MidtransService $midtrans): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_gateway_code' => ['required', 'string', 'exists:payment_gateways,code'],
        ]);

        $gateway = $this->availableGateways()
            ->firstWhere('code', $validated['payment_gateway_code']);

        if (! $gateway instanceof PaymentGateway) {
            throw ValidationException::withMessages([
                'payment_gateway_code' => 'Metode pembayaran tersebut tidak tersedia untuk toko ini.',
            ]);
        }

        $cartItems = $cart->selectedItems();

        if ($cartItems->isEmpty()) {
            return redirect(StorefrontContext::route('cart.index'))
                ->withErrors(['cart' => $cart->items()->isEmpty()
                    ? 'Keranjang masih kosong.'
                    : 'Pilih dulu produk yang mau dipesan.']);
        }

        $order = DB::transaction(function () use ($validated, $gateway, $cartItems): Order {
            $subtotal = 0.0;
            $lines = [];

            foreach ($cartItems as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product->id);
                $variant = $item->variant === null
                    ? null
                    : ProductVariant::query()->lockForUpdate()->find($item->variant->id);
                $label = $item->label();

                if (! $product instanceof Product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => "{$label} sudah tidak tersedia. Periksa keranjang kembali.",
                    ]);
                }

                if ($item->variant !== null && (! $variant instanceof ProductVariant || ! $variant->is_active)) {
                    throw ValidationException::withMessages([
                        'cart' => "Varian {$label} sudah tidak tersedia. Periksa keranjang kembali.",
                    ]);
                }

                $stock = $variant === null ? $product->stock : $variant->stock;

                if ($stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$label} sudah berubah. Periksa keranjang kembali.",
                    ]);
                }

                $unitPrice = (float) ($variant === null ? $product->price : $variant->price);
                $subtotal += $unitPrice * $item->quantity;
                $lines[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                ];
            }

            $order = Order::query()->create([
                ...$validated,
                'number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'demo_store' => StorefrontContext::slug(),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_gateway_code' => $gateway->code,
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_variant_id' => $line['variant']?->id,
                    'product_name' => $line['product']->name,
                    'variant_name' => $line['variant']?->name,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['unit_price'] * $line['quantity'],
                ]);

                // Stock lives on the variant when there is one.
                ($line['variant'] ?? $line['product'])->decrement('stock', $line['quantity']);
            }

            return $order;
        });

        // Only the ordered lines leave the cart; anything left unticked stays.
        $cart->forget($cartItems->map(fn (CartLine $line): string => $line->key)->all());

        if ($gateway->code === MidtransService::GATEWAY_CODE) {
            try {
                $order = $midtrans->createSnapTransaction($order);
            } catch (Throwable $exception) {
                report($exception);

                return redirect($this->successUrl($order))->withErrors([
                    'payment' => 'Pesanan sudah tercatat, tetapi sesi pembayaran belum dapat dibuat. Coba lagi dari halaman ini.',
                ]);
            }
        }

        return redirect($this->successUrl($order));
    }

    public function success(Order $order): View
    {
        if (blank($order->public_token)) {
            $order->forceFill(['public_token' => Str::random(64)])->save();
        }

        $order->load('items');
        $gateway = PaymentGateway::query()->where('code', $order->payment_gateway_code)->first();

        return view('storefront.checkout.success', [
            'order' => $order,
            'gateway' => $gateway,
            'midtransClientKey' => $gateway?->code === MidtransService::GATEWAY_CODE
                ? config('services.midtrans.client_key')
                : null,
            'midtransSnapJsUrl' => $gateway?->code === MidtransService::GATEWAY_CODE
                ? config('services.midtrans.snap_js_url')
                : null,
            'midtransRetryUrl' => $gateway?->code === MidtransService::GATEWAY_CODE
                ? URL::temporarySignedRoute(StorefrontContext::routeName('checkout.midtrans.retry'), now()->addDay(), StorefrontContext::routeParameters(['order' => $order]))
                : null,
            'trackingUrl' => StorefrontContext::route('orders.track', ['token' => $order->public_token]),
            'whatsappTrackingUrl' => $this->whatsappTrackingUrl($order),
        ]);
    }

    public function track(string $token): View
    {
        $order = Order::query()->where('public_token', $token)->firstOrFail();
        $order->load('items');
        $gateway = PaymentGateway::query()->where('code', $order->payment_gateway_code)->first();

        return view('storefront.orders.track', [
            'order' => $order,
            'gateway' => $gateway,
            'timeline' => OrderTimeline::for($order),
            'statusLabel' => OrderTimeline::currentLabel($order),
            'paymentLabel' => OrderTimeline::paymentLabel($order),
            'trackingUrl' => StorefrontContext::route('orders.track', ['token' => $order->public_token]),
            'whatsappTrackingUrl' => $this->whatsappTrackingUrl($order),
        ]);
    }

    public function retryMidtrans(Order $order, MidtransService $midtrans): RedirectResponse
    {
        abort_unless($order->payment_gateway_code === MidtransService::GATEWAY_CODE, 404);

        if ($order->payment_status !== 'pending' || filled($order->payment_checkout_token)) {
            return redirect($this->successUrl($order));
        }

        try {
            $midtrans->createSnapTransaction($order);

            return redirect($this->successUrl($order))->with('success', 'Sesi pembayaran berhasil dibuat.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect($this->successUrl($order))->withErrors([
                'payment' => 'Midtrans masih belum dapat dihubungi. Silakan coba beberapa saat lagi.',
            ]);
        }
    }

    /**
     * Active gateways this storefront offers, in the order shoppers see them.
     *
     * @return Collection<int, PaymentGateway>
     */
    private function availableGateways(): Collection
    {
        $allowedCodes = StorefrontContext::gatewayCodes();

        return PaymentGateway::query()
            ->where('is_active', true)
            ->when($allowedCodes !== null, fn ($query) => $query->whereIn('code', $allowedCodes))
            ->orderByRaw('case when code = ? then 0 else 1 end', [MidtransService::GATEWAY_CODE])
            ->orderBy('name')
            ->get();
    }

    private function successUrl(Order $order): string
    {
        return URL::temporarySignedRoute(
            StorefrontContext::routeName('checkout.success'),
            now()->addDay(),
            StorefrontContext::routeParameters(['order' => $order]),
        );
    }

    private function whatsappTrackingUrl(Order $order): string
    {
        $phone = preg_replace('/\D+/', '', $order->customer_phone) ?? '';
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $message = "Halo {$order->customer_name}, ini link aman untuk memantau order {$order->number}: ".StorefrontContext::route('orders.track', ['token' => $order->public_token]);

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }
}
