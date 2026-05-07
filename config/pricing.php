<?php

/**
 * Public pricing matrix used by the home, pricing, and store-setup views.
 *
 * Display only — subscription billing still uses the INR value stored on
 * each plan row (see plans table, price_monthly / price_yearly).
 */
return [
    'currencies' => [
        'INR' => ['label' => 'INR (₹)',   'symbol' => '₹'],
        'USD' => ['label' => 'USD ($)',   'symbol' => '$'],
        'EUR' => ['label' => 'EUR (€)',   'symbol' => '€'],
        'AED' => ['label' => 'AED (د.إ)', 'symbol' => 'د.إ '],
    ],

    'default_currency' => 'INR',

    'plans' => [
        'starter'    => [
            'monthly' => ['INR' => 89,    'USD' => 20,   'EUR' => 18,   'AED' => 25],
            'yearly'  => ['INR' => 890,   'USD' => 200,  'EUR' => 180,  'AED' => 250],
        ],
        'pro'        => [
            'monthly' => ['INR' => 249,   'USD' => 59,   'EUR' => 49,   'AED' => 75],
            'yearly'  => ['INR' => 2490,  'USD' => 590,  'EUR' => 490,  'AED' => 750],
        ],
        'business'   => [
            'monthly' => ['INR' => 699,   'USD' => 159,  'EUR' => 139,  'AED' => 199],
            'yearly'  => ['INR' => 6990,  'USD' => 1590, 'EUR' => 1390, 'AED' => 1990],
        ],
        'enterprise' => [
            'monthly' => ['INR' => 1799,  'USD' => 399,  'EUR' => 359,  'AED' => 499],
            'yearly'  => ['INR' => 17990, 'USD' => 3990, 'EUR' => 3590, 'AED' => 4990],
        ],
    ],
];
