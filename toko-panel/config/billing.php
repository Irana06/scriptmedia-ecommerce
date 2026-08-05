<?php

return [
    'invoice_lead_days' => (int) env('BILLING_INVOICE_LEAD_DAYS', 3),
    'grace_period_days' => (int) env('BILLING_GRACE_PERIOD_DAYS', 3),
    'suspend_after_days' => (int) env('BILLING_SUSPEND_AFTER_DAYS', 10),
    'schedule_timezone' => env('BILLING_SCHEDULE_TIMEZONE', 'Asia/Jakarta'),
];
