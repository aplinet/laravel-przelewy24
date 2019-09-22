<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Endpoint Mode
    |--------------------------------------------------------------------------
    |
    | This setting determine which endpoint will be used to process
    | transactions. Available options: live, sandbox
    |
    */

    'mode' => env('PRZELEWY24_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Merchant ID
    |--------------------------------------------------------------------------
    |
    | Merchant ID available in the payment provider panel after logging in.
    |
    */

    'merchant_id' => env('PRZELEWY24_MERCHANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Pos ID
    |--------------------------------------------------------------------------
    |
    | Store identifier for which upcoming transactions will be processed 
    | available in the payment provider panel after logging in.
    | If the merchant ID is provided, transactions will not be assigned 
    | to any store.
    |
    */

    'pos_id' => env('PRZELEWY24_POS_ID', env('PRZELEWY24_MERCHANT_ID', '')),

    /*
    |--------------------------------------------------------------------------
    | CRC
    |--------------------------------------------------------------------------
    |
    | The string of characters available in the panel on the payment 
    | provider's website for signing the transaction.
    |
    */

    'crc' => env('PRZELEWY24_CRC', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Return Route
    |--------------------------------------------------------------------------
    |
    | The default routing name to which the user will be automatically 
    | redirected after payment.
    |
    */

    'return_route' => null,

    /*
    |--------------------------------------------------------------------------
    | Disable Package Routes
    |--------------------------------------------------------------------------
    |
    | Here you can disable the default transaction processor provided 
    | with this package.
    |
    */

    'package_routes' => false
];