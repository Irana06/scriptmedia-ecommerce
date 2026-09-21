<?php

return [
    /*
    | How long an order may sit unpaid before it is cancelled and its stock goes
    | back to the catalogue. Keep this aligned with the Midtrans Snap expiry so a
    | shopper never pays for an order that has already been released.
    */
    'payment_window_hours' => env('STORE_PAYMENT_WINDOW_HOURS', 24),
];
