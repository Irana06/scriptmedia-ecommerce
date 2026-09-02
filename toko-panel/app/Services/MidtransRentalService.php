<?php

namespace App\Services;

use App\Models\RentalOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransRentalService
{
    public function isConfigured(): bool
    {
        return filled(config('services.midtrans.client_key')) && filled(config('services.midtrans.server_key'));
    }

    public function createSnapTransaction(RentalOrder $order): RentalOrder
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Kredensial Midtrans belum dikonfigurasi.');
        }

        $order->loadMissing(['user', 'plan']);
        $response = Http::acceptJson()->asJson()
            ->withBasicAuth((string) config('services.midtrans.server_key'), '')
            ->connectTimeout(5)->timeout(15)
            ->post((string) config('services.midtrans.snap_url'), [
                'transaction_details' => [
                    'order_id' => $order->number,
                    'gross_amount' => (int) round((float) $order->amount),
                ],
                'item_details' => [[
                    'id' => 'plan-'.$order->plan_id,
                    'price' => (int) round((float) $order->amount),
                    'quantity' => 1,
                    'name' => Str::limit('Sewa Toko '.Str::title($order->plan->name).' - '.$order->billing_cycle, 50, ''),
                ]],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->whatsapp,
                ],
                'credit_card' => ['secure' => true],
                'callbacks' => [
                    'finish' => URL::route('portal.orders.show', $order),
                ],
                'expiry' => ['duration' => 24, 'unit' => 'hours'],
            ]);

        $response->throw();
        $token = $response->json('token');
        $redirectUrl = $response->json('redirect_url');

        if (! is_string($token) || $token === '' || ! is_string($redirectUrl) || $redirectUrl === '') {
            throw new RuntimeException('Respons Midtrans tidak memuat token pembayaran yang valid.');
        }

        $order->update(['payment_checkout_token' => $token, 'payment_checkout_url' => $redirectUrl]);

        return $order->refresh();
    }

    /** @param array<string, mixed> $notification */
    public function hasValidSignature(array $notification): bool
    {
        $signature = $notification['signature_key'] ?? null;
        if (! is_string($signature) || $signature === '' || blank(config('services.midtrans.server_key'))) {
            return false;
        }

        $expected = hash('sha512', (string) ($notification['order_id'] ?? '').(string) ($notification['status_code'] ?? '').(string) ($notification['gross_amount'] ?? '').(string) config('services.midtrans.server_key'));

        return hash_equals($expected, $signature);
    }

    /** @param array<string, mixed> $notification */
    public function hasExpectedMerchant(array $notification): bool
    {
        $expectedMerchantId = config('services.midtrans.merchant_id');

        if (blank($expectedMerchantId)) {
            return true;
        }

        return isset($notification['merchant_id'])
            && is_string($notification['merchant_id'])
            && hash_equals((string) $expectedMerchantId, $notification['merchant_id']);
    }

    /** @param array<string, mixed> $notification */
    public function applyNotification(RentalOrder $order, array $notification): RentalOrder
    {
        return DB::transaction(function () use ($order, $notification): RentalOrder {
            $locked = RentalOrder::query()->lockForUpdate()->findOrFail($order->id);
            if (number_format((float) $locked->amount, 2, '.', '') !== number_format((float) $notification['gross_amount'], 2, '.', '')) {
                throw new RuntimeException('Nominal notifikasi Midtrans tidak sesuai.');
            }

            $transactionStatus = $notification['transaction_status'] ?? null;
            $isPaid = $transactionStatus === 'settlement' || ($transactionStatus === 'capture' && ($notification['fraud_status'] ?? null) === 'accept');
            $isFailed = in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true);

            $locked->update([
                'status' => $isPaid ? 'paid' : ($isFailed ? 'cancelled' : $locked->status),
                'payment_reference' => $notification['transaction_id'] ?? $locked->payment_reference,
                'payment_metadata' => [
                    'transaction_status' => $transactionStatus,
                    'payment_type' => $notification['payment_type'] ?? null,
                    'notified_at' => now()->toIso8601String(),
                ],
                'paid_at' => $isPaid ? ($locked->paid_at ?? now()) : $locked->paid_at,
            ]);

            return $locked->refresh();
        });
    }
}
