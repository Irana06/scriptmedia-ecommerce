<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\RentalOrder;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RentalOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_seeder_safely_synchronizes_the_canonical_packages(): void
    {
        Plan::factory()->create([
            'slug' => 'starter',
            'price_platform' => 1000000,
            'price_care_monthly' => 750000,
            'is_active' => false,
        ]);

        $this->seed(PlanSeeder::class);

        $this->assertDatabaseCount('plans', 3);
        $this->assertDatabaseHas('plans', ['slug' => 'starter', 'price_platform' => 150000, 'price_care_monthly' => 150000, 'is_active' => true]);
        $this->assertDatabaseHas('plans', ['slug' => 'standard', 'price_platform' => 150000, 'price_care_monthly' => 350000, 'is_active' => true]);
        $this->assertDatabaseHas('plans', ['slug' => 'pro', 'price_platform' => 150000, 'price_care_monthly' => 550000, 'is_active' => true]);
    }

    public function test_public_home_displays_active_plans(): void
    {
        Plan::factory()->create();
        Plan::factory()->standard()->create();
        Plan::factory()->pro()->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('Standard')
            ->assertSee('Pro')
            ->assertSee('QRIS otomatis')
            ->assertSee('QRIS + transfer bank')
            ->assertSee('Ongkir otomatis real-time dan kustomisasi desain penuh')
            ->assertSee('retensi 14 hari')
            ->assertSee('Apa saja yang termasuk?');
    }

    public function test_public_guide_explains_the_business_flow_and_live_plans(): void
    {
        Plan::factory()->create();
        Plan::factory()->standard()->create();
        Plan::factory()->pro()->create();

        $this->get(route('guide'))
            ->assertOk()
            ->assertSee('Alur bisnis')
            ->assertSee('Toko Panel')
            ->assertSee('Toko Engine')
            ->assertSee('Peta proses bergaya BPMN')
            ->assertSee('Pembayaran')
            ->assertSee('terverifikasi')
            ->assertSee('Buat database')
            ->assertSee('tenant terpisah')
            ->assertSee('Toko aktif')
            ->assertSee('Starter')
            ->assertSee('Standard')
            ->assertSee('Pro')
            ->assertSee('QRIS otomatis')
            ->assertSee('QRIS + transfer bank otomatis')
            ->assertSee('Backup mingguan')
            ->assertSee('retensi 14 hari')
            ->assertSee('Laporan bulanan detail + konsultasi bulanan 30 menit')
            ->assertSee('Cetak / simpan PDF');
    }

    public function test_owner_can_create_monthly_rental_order(): void
    {
        $owner = User::factory()->owner()->create();
        $plan = Plan::factory()->standard()->create();

        $response = $this->actingAs($owner)->post(route('onboarding.store', $plan), [
            'business_name' => 'Toko Senja',
            'desired_subdomain' => 'toko-senja',
            'custom_domain' => 'tokosenja.id',
            'whatsapp' => '+628123456789',
            'billing_cycle' => 'monthly',
            'notes' => 'Warna utama biru.',
        ]);

        $order = RentalOrder::query()->sole();
        $response->assertRedirect(route('portal.orders.show', $order));
        $this->assertSame('500000.00', $order->amount);
        $this->assertSame('awaiting_payment', $order->status);
    }

    public function test_annual_order_charges_ten_months(): void
    {
        $owner = User::factory()->owner()->create();
        $plan = Plan::factory()->pro()->create();

        $this->actingAs($owner)->post(route('onboarding.store', $plan), [
            'business_name' => 'Toko Tahunan',
            'desired_subdomain' => 'toko-tahunan',
            'whatsapp' => '628123456780',
            'billing_cycle' => 'annual',
        ])->assertRedirect();

        $this->assertSame('7000000.00', RentalOrder::query()->sole()->amount);
    }

    public function test_starter_rejects_custom_domain(): void
    {
        $owner = User::factory()->owner()->create();
        $plan = Plan::factory()->create();

        $this->actingAs($owner)->from(route('onboarding.create', $plan))->post(route('onboarding.store', $plan), [
            'business_name' => 'Toko Starter',
            'desired_subdomain' => 'toko-starter',
            'custom_domain' => 'starter.id',
            'whatsapp' => '628123456781',
            'billing_cycle' => 'monthly',
        ])->assertRedirect(route('onboarding.create', $plan))->assertSessionHasErrors('custom_domain');

        $this->assertDatabaseCount('rental_orders', 0);
    }

    public function test_midtrans_notification_marks_matching_order_as_paid(): void
    {
        config(['services.midtrans.server_key' => 'server-key']);
        Http::preventStrayRequests();
        $owner = User::factory()->owner()->create();
        $plan = Plan::factory()->create();
        $order = RentalOrder::create([
            'number' => 'RENT-TEST-001',
            'user_id' => $owner->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'business_name' => 'Toko Test',
            'desired_subdomain' => 'toko-test-webhook',
            'whatsapp' => '628123456782',
            'status' => 'awaiting_payment',
            'amount' => 300000,
            'payment_gateway' => 'midtrans',
        ]);
        $grossAmount = '300000.00';

        $response = $this->postJson(route('payments.midtrans.rental-notification'), [
            'order_id' => $order->number,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $order->number.'200'.$grossAmount.'server-key'),
            'transaction_id' => 'midtrans-test-001',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ]);

        $response->assertOk();
        $this->assertSame('paid', $order->refresh()->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_midtrans_notification_rejects_a_different_merchant(): void
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.merchant_id' => 'expected-merchant',
        ]);
        $owner = User::factory()->owner()->create();
        $plan = Plan::factory()->create();
        $order = RentalOrder::create([
            'number' => 'RENT-TEST-MERCHANT',
            'user_id' => $owner->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'business_name' => 'Toko Merchant',
            'desired_subdomain' => 'toko-merchant',
            'whatsapp' => '628123456784',
            'status' => 'awaiting_payment',
            'amount' => 300000,
            'payment_gateway' => 'midtrans',
        ]);
        $grossAmount = '300000.00';

        $this->postJson(route('payments.midtrans.rental-notification'), [
            'order_id' => $order->number,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $order->number.'200'.$grossAmount.'server-key'),
            'transaction_id' => 'midtrans-test-merchant',
            'transaction_status' => 'settlement',
            'merchant_id' => 'other-merchant',
        ])->assertForbidden();

        $this->assertSame('awaiting_payment', $order->refresh()->status);
    }

    public function test_owner_cannot_open_another_owners_order(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $plan = Plan::factory()->create();
        $order = RentalOrder::create([
            'number' => 'RENT-TEST-002',
            'user_id' => $owner->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'business_name' => 'Private Store',
            'desired_subdomain' => 'private-store',
            'whatsapp' => '628123456783',
            'status' => 'awaiting_payment',
            'amount' => 300000,
            'payment_gateway' => 'midtrans',
        ]);

        $this->actingAs($otherOwner)->get(route('portal.orders.show', $order))->assertForbidden();
    }
}
