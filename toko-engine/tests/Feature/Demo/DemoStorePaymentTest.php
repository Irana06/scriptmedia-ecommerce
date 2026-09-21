<?php

namespace Tests\Feature\Demo;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Services\MidtransService;
use Database\Seeders\DemoStoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DemoStorePaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string}> */
    public static function planChannelProvider(): array
    {
        return [
            'starter only gets QRIS' => ['starter'],
            'standard adds virtual accounts' => ['standard'],
            'pro leaves every channel open' => ['pro'],
        ];
    }

    #[DataProvider('planChannelProvider')]
    public function test_each_demo_store_sends_its_plan_channels_to_snap(string $plan): void
    {
        $this->prepareDemo();
        $product = $this->demoProduct($plan);

        $this->post(route('demo.cart.store', ['demoStore' => $plan, 'product' => $product]), ['quantity' => 1]);
        $response = $this->post(route('demo.checkout.store', ['demoStore' => $plan]), $this->customerPayload());

        $response->assertRedirect();

        $expected = config("store-limits.midtrans_payment_methods.{$plan}");

        Http::assertSent(function (Request $request) use ($expected): bool {
            $sent = $request->data()['enabled_payments'] ?? null;

            return $expected === null ? $sent === null : $sent === $expected;
        });
    }

    public function test_snap_sends_shoppers_back_to_the_demo_store_they_started_from(): void
    {
        $this->prepareDemo();
        $product = $this->demoProduct('standard');

        $this->post(route('demo.cart.store', ['demoStore' => 'standard', 'product' => $product]), ['quantity' => 1]);
        $this->post(route('demo.checkout.store', ['demoStore' => 'standard']), $this->customerPayload());

        Http::assertSent(function (Request $request): bool {
            $finish = $request->data()['callbacks']['finish'] ?? null;

            return is_string($finish) && str_contains($finish, '/standard/orders/');
        });
    }

    public function test_demo_checkout_only_offers_the_automatic_gateway(): void
    {
        $this->prepareDemo();
        $product = $this->demoProduct('starter');

        $this->post(route('demo.cart.store', ['demoStore' => 'starter', 'product' => $product]), ['quantity' => 1]);

        $this->get(route('demo.checkout.create', ['demoStore' => 'starter']))
            ->assertOk()
            ->assertSee('Midtrans')
            ->assertDontSee('Transfer Bank Manual')
            // A single gateway is information, not a choice, so no radio is rendered.
            ->assertDontSee('type="radio"', false);
    }

    #[DataProvider('planChannelProvider')]
    public function test_the_storefront_never_shows_plan_or_sandbox_wording(string $plan): void
    {
        $this->prepareDemo();
        $product = $this->demoProduct($plan);

        $this->post(route('demo.cart.store', ['demoStore' => $plan, 'product' => $product]), ['quantity' => 1]);

        foreach ([
            route('demo.home', ['demoStore' => $plan]),
            route('demo.products.index', ['demoStore' => $plan]),
            route('demo.checkout.create', ['demoStore' => $plan]),
        ] as $url) {
            $response = $this->get($url)->assertOk();

            foreach (['Paket toko', 'Sandbox', 'Biaya bulanan', 'Web Care', 'Kapasitas katalog'] as $forbidden) {
                $response->assertDontSee($forbidden);
            }
        }
    }

    public function test_buy_now_sends_the_shopper_straight_to_checkout(): void
    {
        $this->prepareDemo();
        $product = $this->demoProduct('standard');

        $this->post(route('demo.cart.buy', ['demoStore' => 'standard', 'product' => $product]), ['quantity' => 2])
            ->assertRedirect(route('demo.checkout.create', ['demoStore' => 'standard']));

        $this->get(route('demo.checkout.create', ['demoStore' => 'standard']))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('&times; 2', false);
    }

    public function test_a_gateway_outside_the_plan_is_rejected_on_a_demo_store(): void
    {
        $this->prepareDemo();
        $product = $this->demoProduct('starter');

        $this->post(route('demo.cart.store', ['demoStore' => 'starter', 'product' => $product]), ['quantity' => 1]);

        $this->post(
            route('demo.checkout.store', ['demoStore' => 'starter']),
            [...$this->customerPayload(), 'payment_gateway_code' => 'manual-transfer'],
        )->assertSessionHasErrors('payment_gateway_code');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_settled_payment_moves_the_order_into_processing(): void
    {
        $this->prepareDemo();
        $order = Order::query()->create([
            'number' => 'ORD-DEMO-0001',
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
        $grossAmount = '100000.00';
        $notification = [
            'order_id' => $order->number,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $order->number.'200'.$grossAmount.'server-test'),
            'merchant_id' => 'merchant-test',
            'transaction_id' => 'midtrans-transaction-id',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'other_qris',
        ];

        $this->postJson(route('payments.midtrans.notification'), $notification)->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
    }

    private function prepareDemo(): void
    {
        config()->set('database.connections.central.database', null);
        config()->set('services.midtrans', [
            'merchant_id' => 'merchant-test',
            'client_key' => 'client-test',
            'server_key' => 'server-test',
            'is_production' => false,
            'snap_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions',
            'snap_js_url' => 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'demo-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/demo-snap-token',
            ], 201),
        ]);

        $this->seed(DemoStoreSeeder::class);

        PaymentGateway::query()->create([
            'code' => MidtransService::GATEWAY_CODE,
            'name' => 'Midtrans',
            'is_active' => true,
        ]);
        PaymentGateway::query()->create([
            'code' => 'manual-transfer',
            'name' => 'Transfer Bank Manual',
            'is_active' => true,
        ]);
    }

    private function demoProduct(string $plan): Product
    {
        return Product::query()->where('slug', 'like', $plan.'-%')->where('stock', '>', 0)->firstOrFail();
    }

    /** @return array<string, string> */
    private function customerPayload(): array
    {
        return [
            'customer_name' => 'Ayu Pelanggan',
            'customer_email' => 'ayu@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh No. 1',
            'payment_gateway_code' => MidtransService::GATEWAY_CODE,
        ];
    }
}
