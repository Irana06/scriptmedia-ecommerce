<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Services\CartService;
use App\Services\MidtransService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function create(CartService $cart): View|RedirectResponse
    {
        if ($cart->items()->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        $gateways = PaymentGateway::query()->where('is_active', true)->orderBy('name')->get();

        return view('storefront.checkout.create', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'gateways' => $gateways,
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

        $gateway = PaymentGateway::query()
            ->where('code', $validated['payment_gateway_code'])
            ->where('is_active', true)
            ->firstOrFail();
        $cartItems = $cart->items();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang masih kosong.']);
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
                    'payment' => 'Order sudah tercatat, tetapi sesi Midtrans belum dapat dibuat. Coba lagi dari halaman ini.',
                ]);
            }
        }

        return redirect($this->successUrl($order));
    }

    public function success(Order $order): View
    {
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
                ? URL::temporarySignedRoute('checkout.midtrans.retry', now()->addDay(), ['order' => $order])
                : null,
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

            return redirect($this->successUrl($order))->with('success', 'Sesi pembayaran Midtrans berhasil dibuat.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect($this->successUrl($order))->withErrors([
                'payment' => 'Midtrans masih belum dapat dihubungi. Silakan coba beberapa saat lagi.',
            ]);
        }
    }

    private function successUrl(Order $order): string
    {
        return URL::temporarySignedRoute(
            'checkout.success',
            now()->addDay(),
            ['order' => $order],
        );
    }
}
