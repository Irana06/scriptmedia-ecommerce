<?php

namespace App\Support;

/**
 * Indonesian labels for the status values stored in English.
 *
 * Kept in one place so the same status never reads differently on two screens,
 * and so an unmapped value degrades to something readable instead of blank.
 */
class StatusLabel
{
    private const LABELS = [
        // Rental order
        'awaiting_payment' => 'Menunggu pembayaran',
        'paid' => 'Dibayar',
        'provisioning' => 'Sedang disiapkan',
        'ready' => 'Siap dipakai',
        'cancelled' => 'Dibatalkan',

        // Tenant provisioning
        'pending' => 'Menunggu',
        'active' => 'Aktif',
        'failed' => 'Gagal',

        // Store status
        'grace_period' => 'Masa tenggang',
        'suspended' => 'Ditangguhkan',

        // Invoice and payment
        'unpaid' => 'Belum dibayar',
        'overdue' => 'Jatuh tempo',
        'refunded' => 'Dikembalikan',
        'settlement' => 'Lunas',
        'expire' => 'Kedaluwarsa',

        // Content change request
        'in_progress' => 'Sedang dikerjakan',
        'done' => 'Selesai',
        'completed' => 'Selesai',
        'rejected' => 'Ditolak',
    ];

    public static function for(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        return self::LABELS[$status] ?? str($status)->replace('_', ' ')->title()->toString();
    }
}
