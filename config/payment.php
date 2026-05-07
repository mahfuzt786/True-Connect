<?php

return [
    'stripe' => [
        'key'            => env('STRIPE_KEY', ''),
        'secret'         => env('STRIPE_SECRET', ''),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        'currency'       => 'usd',
    ],
    'razorpay' => [
        'key_id'         => env('RAZORPAY_KEY_ID', ''),
        'key_secret'     => env('RAZORPAY_KEY_SECRET', ''),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
        'currency'       => 'INR',
    ],
    'paypal' => [
        'client_id'      => env('PAYPAL_CLIENT_ID', ''),
        'client_secret'  => env('PAYPAL_CLIENT_SECRET', ''),
        'mode'           => env('PAYPAL_MODE', 'sandbox'),
        'currency'       => 'USD',
        'webhook_id'     => env('PAYPAL_WEBHOOK_ID', ''),
    ],
    'default' => env('DEFAULT_PAYMENT_GATEWAY', 'stripe'),
];
