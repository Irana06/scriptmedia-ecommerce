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
        'plan_slug' => env('STORE_PLAN_SLUG'),
        'max_products' => env('STORE_MAX_PRODUCTS'),
        'max_payment_gateways' => env('STORE_MAX_PAYMENT_GATEWAYS'),
    ],

    'midtrans_payment_methods' => [
        'starter' => ['other_qris'],
        'standard' => [
            'other_qris',
            'echannel',
            'permata_va',
            'bca_va',
            'bni_va',
            'bri_va',
            'cimb_va',
            'danamon_va',
            'bsi_va',
            'seabank_va',
            'saqu_va',
            'other_va',
        ],
    ],
];
