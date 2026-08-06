<?php

namespace Tests\Feature\Payments;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_route_is_excluded_from_request_forgery_protection(): void
    {
        $route = app('router')->getRoutes()->getByName('payments.midtrans.notification');

        $this->assertNotNull($route);
        $this->assertContains(PreventRequestForgery::class, $route->excludedMiddleware());
    }

    public function test_checkout_creates_a_sandbox_snap_transaction(): void
    {
        $this->configureMidtrans();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'sandbox-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/sandbox-snap-token',
            ], 201),
        ]);
        $category = Category::query()->create(['name' => 'Rumah', 'slug' => 'rumah', 'is_active' => true]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Lampu Senja',
            'slug' => 'lampu-senja',
            'price' => 125000,
            'stock' => 3,
            'is_active' => true,
        ]);
        PaymentGateway::query()->create([
            'code' => MidtransService::GATEWAY_CODE,
            'name' => 'Midtrans',
            'is_active' => true,
        ]);

        $this->post(route('cart.store', $product), ['quantity' => 2]);
        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Ayu Pelanggan',
            'customer_email' => 'ayu@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh No. 1',
            'payment_gateway_code' => MidtransService::GATEWAY_CODE,
        ]);

        $order = Order::query()->firstOrFail();
        $response->assertRedirect();
        $this->assertSame('sandbox-snap-token', $order->payment_checkout_token);
        $this->assertSame(250000.0, (float) $order->total);
        $this->get((string) $response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Bayar sekarang dengan Midtrans');

        Http::assertSent(function (Request $request) use ($order): bool {
            return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
                && $request['transaction_details']['order_id'] === $order->number
                && $request['transaction_details']['gross_amount'] === 250000
                && $request['credit_card']['secure'] === true;
        });
    }

    public function test_valid_notification_is_idempotent_and_cannot_downgrade_a_paid_order(): void
    {
        $this->configureMidtrans();
        $order = $this->midtransOrder();
        $settlement = $this->notification($order, 'settlement', '200');

        $this->postJson(route('payments.midtrans.notification'), $settlement)
            ->assertOk()
            ->assertJson(['message' => 'OK']);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'payment_reference' => 'midtrans-transaction-id',
        ]);
        $this->assertNotNull($order->fresh()->paid_at);

        $this->postJson(route('payments.midtrans.notification'), $settlement)->assertOk();
        $pending = $this->notification($order, 'pending', '201');
        $this->postJson(route('payments.midtrans.notification'), $pending)->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_notification_with_invalid_signature_is_rejected(): void
    {
        $this->configureMidtrans();
        $order = $this->midtransOrder();
        $notification = $this->notification($order, 'settlement', '200');
        $notification['signature_key'] = 'invalid-signature';

        $this->postJson(route('payments.midtrans.notification'), $notification)->assertForbidden();
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    private function configureMidtrans(): void
    {
        config()->set('services.midtrans', [
            'merchant_id' => 'merchant-test',
            'client_key' => 'client-test',
            'server_key' => 'server-test',
            'is_production' => false,
            'snap_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions',
            'snap_js_url' => 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);
    }

    private function midtransOrder(): Order
    {
        return Order::query()->create([
            'number' => 'ORD-MIDTRANS-001',
            'customer_name' => 'Ayu',
            'customer_email' => 'ayu@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh',
            'subtotal' => 100000,
            'total' => 100000,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_gateway_code' => MidtransService::GATEWAY_CODE,
            'placed_at' => now(),
        ]);
    }

    /** @return array<string, string> */
    private function notification(Order $order, string $transactionStatus, string $statusCode): array
    {
        $grossAmount = '100000.00';

        return [
            'order_id' => $order->number,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $order->number.$statusCode.$grossAmount.'server-test'),
            'merchant_id' => 'merchant-test',
            'transaction_id' => 'midtrans-transaction-id',
            'transaction_status' => $transactionStatus,
            'fraud_status' => 'accept',
            'payment_type' => 'bank_transfer',
        ];
    }
}
