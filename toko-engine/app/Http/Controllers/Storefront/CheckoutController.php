<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\StoreLimitService;
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
        if ($cart->items()->isEmpty()) {
            return redirect(StorefrontContext::route('cart.index'))->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        return view('storefront.checkout.create', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
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

        $cartItems = $cart->items();

        if ($cartItems->isEmpty()) {
            return redirect(StorefrontContext::route('cart.index'))->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        $order = DB::transaction(function () use ($validated, $gateway, $cartItems): Order {
            $subtotal = 0.0;
            $lockedProducts = [];

            foreach ($cartItems as $item) {
                $product = Product::query()->lockForUpdate()->find($item['product']->id);

                if (! $product instanceof Product || ! $product->is_active || $product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$item['product']->name} sudah berubah. Periksa keranjang kembali.",
                    ]);
                }

                $lockedProducts[$product->id] = $product;
                $subtotal += (float) $product->price * $item['quantity'];
            }

            $order = Order::query()->create([
                ...$validated,
                'number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_gateway_code' => $gateway->code,
                'placed_at' => now(),
            ]);

            foreach ($cartItems as $item) {
                $product = $lockedProducts[$item['product']->id];
                $lineTotal = (float) $product->price * $item['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ]);
                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        $cart->clear();

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
