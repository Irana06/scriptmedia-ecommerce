<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    public const GATEWAY_CODE = 'midtrans';

    public function __construct(private readonly StoreLimitService $storeLimits) {}

    public function isConfigured(): bool
    {
        return filled(config('services.midtrans.client_key'))
            && filled(config('services.midtrans.server_key'));
    }

    public function createSnapTransaction(Order $order): Order
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Kredensial Midtrans belum dikonfigurasi.');
        }

        $order->loadMissing('items');
        $payload = [
            'transaction_details' => [
                'order_id' => $order->number,
                'gross_amount' => (int) round((float) $order->total),
            ],
            'item_details' => $order->items->map(fn ($item): array => [
                'id' => (string) ($item->product_id ?? $item->id),
                'price' => (int) round((float) $item->unit_price),
                'quantity' => $item->quantity,
                'name' => Str::limit($item->product_name, 50, ''),
            ])->all(),
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'shipping_address' => [
                    'first_name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'address' => $order->shipping_address,
                ],
            ],
            'credit_card' => ['secure' => true],
            'callbacks' => [
                'finish' => URL::temporarySignedRoute(
                    'checkout.success',
                    now()->addDay(),
                    ['order' => $order],
                ),
            ],
            'expiry' => ['duration' => 24, 'unit' => 'hours'],
        ];
        $enabledPayments = $this->storeLimits->midtransPaymentMethods();

        if ($enabledPayments !== null) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        $response = Http::acceptJson()
            ->asJson()
            ->withBasicAuth((string) config('services.midtrans.server_key'), '')
            ->connectTimeout(5)
            ->timeout(15)
            ->post((string) config('services.midtrans.snap_url'), $payload);

        $response->throw();
        $token = $response->json('token');
        $redirectUrl = $response->json('redirect_url');

        if (! is_string($token) || $token === '' || ! is_string($redirectUrl) || $redirectUrl === '') {
            throw new RuntimeException('Respons Midtrans tidak memuat token pembayaran yang valid.');
        }

        $order->update([
            'payment_checkout_token' => $token,
            'payment_checkout_url' => $redirectUrl,
        ]);

        return $order->refresh();
    }

    /** @param array<string, mixed> $notification */
    public function hasValidSignature(array $notification): bool
    {
        $signature = $notification['signature_key'] ?? null;

        if (! is_string($signature) || $signature === '' || blank(config('services.midtrans.server_key'))) {
            return false;
        }

        $expected = hash('sha512',
            (string) ($notification['order_id'] ?? '')
            .(string) ($notification['status_code'] ?? '')
            .(string) ($notification['gross_amount'] ?? '')
            .(string) config('services.midtrans.server_key'),
        );

        return hash_equals($expected, $signature);
    }

    /** @param array<string, mixed> $notification */
    public function matchesMerchant(array $notification): bool
    {
        $configuredMerchant = config('services.midtrans.merchant_id');
        $notificationMerchant = $notification['merchant_id'] ?? null;

        return blank($configuredMerchant)
            || ! is_string($notificationMerchant)
            || hash_equals((string) $configuredMerchant, $notificationMerchant);
    }

    /** @param array<string, mixed> $notification */
    public function amountMatches(Order $order, array $notification): bool
    {
        $notifiedAmount = $notification['gross_amount'] ?? null;

        return is_numeric($notifiedAmount)
            && number_format((float) $order->total, 2, '.', '') === number_format((float) $notifiedAmount, 2, '.', '');
    }

    /** @param array<string, mixed> $notification */
    public function applyNotification(Order $order, array $notification): Order
    {
        return DB::transaction(function () use ($order, $notification): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $nextStatus = $this->paymentStatus($notification);
            $currentStatus = $lockedOrder->payment_status;

            if ($currentStatus === 'refunded') {
                $nextStatus = 'refunded';
            } elseif ($currentStatus === 'paid' && in_array($nextStatus, ['pending', 'failed'], true)) {
                $nextStatus = 'paid';
            }

            $lockedOrder->update([
                'payment_status' => $nextStatus ?? $currentStatus,
                'payment_reference' => $notification['transaction_id'] ?? $lockedOrder->payment_reference,
                'payment_metadata' => [
                    'transaction_status' => $notification['transaction_status'] ?? null,
                    'fraud_status' => $notification['fraud_status'] ?? null,
                    'payment_type' => $notification['payment_type'] ?? null,
                    'status_code' => $notification['status_code'] ?? null,
                    'notified_at' => now()->toIso8601String(),
                ],
                'paid_at' => $nextStatus === 'paid' ? ($lockedOrder->paid_at ?? now()) : $lockedOrder->paid_at,
            ]);

            return $lockedOrder->refresh();
        });
    }

    /** @param array<string, mixed> $notification */
    private function paymentStatus(array $notification): ?string
    {
        $transactionStatus = $notification['transaction_status'] ?? null;
        $fraudStatus = $notification['fraud_status'] ?? null;

        return match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'accept' ? 'paid' : ($fraudStatus === 'deny' ? 'failed' : 'pending'),
            'pending' => 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'failed',
            'refund', 'partial_refund', 'chargeback', 'partial_chargeback' => 'refunded',
            default => null,
        };
    }
}
