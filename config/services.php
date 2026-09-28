<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],
    'revenuecat' => [
        'secret_api_key' => env('REVENUECAT_SECRET_API_KEY'),
        'webhook_authorization' => env('REVENUECAT_WEBHOOK_AUTHORIZATION'),
        'entitlement_id' => env('REVENUECAT_PREMIUM_ENTITLEMENT', 'premium'),
        'app_id' => env('REVENUECAT_APP_ID'),
        'products' => [
            'monthly' => env('REVENUECAT_PRODUCT_MONTHLY'),
            'six_months' => env('REVENUECAT_PRODUCT_SIX_MONTHS'),
            'lifetime' => env('REVENUECAT_PRODUCT_LIFETIME'),
        ],
        'api_url' => 'https://api.revenuecat.com/v1',
    ],
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],
    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'url' => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/chat/completions'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

];
