<?php

declare(strict_types=1);

return [
    'driver' => 'smsa',
    'base_url' => env('SMSA_BASE_URL', 'https://track.smsaexpress.com/SecomRestWebApi/api'),
    'timeout' => (int) env('SMSA_TIMEOUT', 20),
    'passkey' => env('SMSA_PASSKEY'),
    'token' => env('SMSA_PASSKEY'),
    'status_map' => [
        'DATA RECEIVED' => 'created',
        'PICKED UP' => 'picked_up',
        'IN TRANSIT' => 'in_transit',
        'OUT FOR DELIVERY' => 'out_for_delivery',
        'DELIVERED' => 'delivered',
        'RETURNED' => 'returned',
        'EXCEPTION' => 'exception',
    ],
];
