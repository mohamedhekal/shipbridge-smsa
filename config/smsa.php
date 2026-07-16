<?php

declare(strict_types=1);

return [
    'driver' => 'smsa',

    /*
    |--------------------------------------------------------------------------
    | SMSA Express SECOM SOAP
    |--------------------------------------------------------------------------
    */
    'wsdl' => env('SMSA_WSDL', 'http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?WSDL'),
    'timeout' => (int) env('SMSA_TIMEOUT', 45),
    'passkey' => env('SMSA_PASSKEY'),
    'pass_key' => env('SMSA_PASSKEY'),
    'token' => env('SMSA_PASSKEY'),

    'ship_type' => env('SMSA_SHIP_TYPE', 'DLV'),

    /*
    | Use FakeSmsaGateway when true (tests / local without SOAP).
    */
    'fake' => (bool) env('SMSA_FAKE', false),

    'status_map' => [
        'DATA RECEIVED' => 'created',
        'PROOF OF DATA CAPTURE' => 'created',
        'PICKED UP' => 'picked_up',
        'ARRIVED AT FACILITY' => 'in_transit',
        'DEPARTED FACILITY' => 'in_transit',
        'IN TRANSIT' => 'in_transit',
        'OUT FOR DELIVERY' => 'out_for_delivery',
        'DELIVERED' => 'delivered',
        'PROOF OF DELIVERY' => 'delivered',
        'RETURNED' => 'returned',
        'RETURNED TO SHIPPER' => 'returned',
        'CANCELLED' => 'cancelled',
        'EXCEPTION' => 'exception',
        'ON HOLD' => 'exception',
    ],
];
