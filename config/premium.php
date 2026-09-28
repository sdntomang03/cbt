<?php

return [
    'callbacks' => [
        'finish' => env('PREMIUM_PAYMENT_FINISH_URL', 'sahabatkreasianak://payment/success'),
        'unfinish' => env('PREMIUM_PAYMENT_UNFINISH_URL', 'sahabatkreasianak://payment/unfinish'),
        'error' => env('PREMIUM_PAYMENT_ERROR_URL', 'sahabatkreasianak://payment/error'),
    ],
    'plans' => [
        'monthly' => [
            'name' => 'Premium 1 Bulan',
            'amount' => env('PREMIUM_MONTHLY_AMOUNT') !== null ? (int) env('PREMIUM_MONTHLY_AMOUNT') : null,
            'duration_months' => 1,
            'product_id' => env('REVENUECAT_PRODUCT_MONTHLY'),
        ],
        'six_months' => [
            'name' => 'Premium 6 Bulan',
            'amount' => env('PREMIUM_SIX_MONTHS_AMOUNT') !== null ? (int) env('PREMIUM_SIX_MONTHS_AMOUNT') : null,
            'duration_months' => 6,
            'product_id' => env('REVENUECAT_PRODUCT_SIX_MONTHS'),
        ],
        'lifetime' => [
            'name' => 'Premium 1 Tahun',
            'amount' => env('PREMIUM_YEARLY_AMOUNT') !== null ? (int) env('PREMIUM_YEARLY_AMOUNT') : null,
            'duration_months' => 12,
            'product_id' => env('REVENUECAT_PRODUCT_LIFETIME'),
        ],
    ],
];
