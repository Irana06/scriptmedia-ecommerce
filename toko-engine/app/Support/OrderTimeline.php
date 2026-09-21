<?php

namespace App\Support;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;

/**
 * @phpstan-type TimelineStep array{key: string, label: string, description: string, done: bool, current: bool, at: CarbonImmutable|null}
 */
class OrderTimeline
{
    /** Ordered fulfilment stages a shopper is walked through. */
    private const STAGES = ['pending', 'processing', 'shipped', 'completed'];

    /** @return list<TimelineStep> */
    public static function for(Order $order): array
    {
        if ($order->status === 'cancelled') {
            return [
                self::step('pending', 'Pesanan dibuat', 'Pesananmu tercatat di toko.', true, false, $order->placed_at),
                self::step('cancelled', 'Pesanan dibatalkan', 'Pesanan ini tidak dilanjutkan.', false, true, $order->updated_at),
            ];
        }

        $reached = array_search($order->status, self::STAGES, true);
        $reached = $reached === false ? 0 : $reached;
        $paid = $order->payment_status === 'paid';

        $labels = [
            'pending' => ['Pesanan dibuat', 'Pesananmu tercatat dan menunggu pembayaran.', $order->placed_at],
            'processing' => [
                'Pembayaran diterima',
                $paid ? 'Toko sedang menyiapkan pesananmu.' : 'Menunggu pembayaran diselesaikan.',
                $order->paid_at,
            ],
            'shipped' => ['Pesanan dikirim', 'Paket sudah diserahkan ke kurir.', null],
            'completed' => ['Pesanan selesai', 'Paket sudah sampai tujuan.', null],
        ];

        $steps = [];

        foreach (self::STAGES as $index => $stage) {
            [$label, $description, $at] = $labels[$stage];
            $steps[] = self::step($stage, $label, $description, $index < $reached, $index === $reached, $at);
        }

        return $steps;
    }

    /** The stage the order is sitting on right now. */
    public static function currentLabel(Order $order): string
    {
        foreach (self::for($order) as $step) {
            if ($step['current']) {
                return $step['label'];
            }
        }

        return ucfirst($order->status);
    }

    public static function paymentLabel(Order $order): string
    {
        return match ($order->payment_status) {
            'paid' => 'Lunas',
            'failed' => 'Gagal / kedaluwarsa',
            'refunded' => 'Dikembalikan',
            default => 'Menunggu pembayaran',
        };
    }

    /** @return TimelineStep */
    private static function step(string $key, string $label, string $description, bool $done, bool $current, mixed $at): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'done' => $done,
            'current' => $current,
            'at' => self::asDate($at),
        ];
    }

    /** Model date columns reach here as dates or as raw strings, depending on the cast. */
    private static function asDate(mixed $value): ?CarbonImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (InvalidFormatException) {
            return null;
        }
    }
}
