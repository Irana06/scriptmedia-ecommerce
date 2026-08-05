<?php

return [
    'admin' => [
        'email' => env('SEED_ADMIN_EMAIL', 'admin@scriptmedia.test'),
        'password' => env('SEED_ADMIN_PASSWORD', 'password'),
    ],

    'owner' => [
        'email' => env('SEED_OWNER_EMAIL', 'owner@scriptmedia.test'),
        'password' => env('SEED_OWNER_PASSWORD', 'password'),
    ],
];
