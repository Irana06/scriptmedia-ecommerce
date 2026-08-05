<?php

return [
    /*
    | Match this installation to tenants.database_name in the central database.
    | When empty, StoreLimitService uses the current application's database name.
    */
    'tenant_database' => env('CENTRAL_TENANT_DATABASE'),

    /*
    | Standalone fallback. Empty values mean unlimited.
    */
    'fallback' => [
        'max_products' => env('STORE_MAX_PRODUCTS'),
        'max_payment_gateways' => env('STORE_MAX_PAYMENT_GATEWAYS'),
    ],
];
