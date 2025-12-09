<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration settings for payment gateways
    | used in the application. You can specify default gateway, API keys,
    | and other related settings here.
    |
    */

    'default_gateway' => env('PAYMENT_GATEWAY', 'credit_card'),

    'gateways' => [
        'credit_card' => [
            'class' => App\Services\Payment\CreditCardGateway::class,
            'api_key' => env('CREDIT_CARD_API_KEY', ''),
            'api_secret' => env('CREDIT_CARD_API_SECRET', ''),
        ],
        'paypal' => [
            'class' => App\Services\Payment\PaypalGateway::class,
            'client_id' => env('PAYPAL_CLIENT_ID', ''),
            'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        ],
    ],

];
