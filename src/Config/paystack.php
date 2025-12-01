<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paystack API Keys
    |--------------------------------------------------------------------------
    |
    | Your Paystack API keys from your dashboard. Use test keys for development
    | and live keys for production.
    |
    */

    'public_key' => env('PAYSTACK_PUBLIC_KEY'),

    'secret_key' => env('PAYSTACK_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for Paystack API. This should not be changed unless
    | Paystack changes their API endpoint.
    |
    */

    'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),

    /*
    |--------------------------------------------------------------------------
    | Merchant Email
    |--------------------------------------------------------------------------
    |
    | Your merchant email address.
    |
    */

    'merchant_email' => env('PAYSTACK_MERCHANT_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Payment URL
    |--------------------------------------------------------------------------
    |
    | The URL to redirect to after payment.
    |
    */

    'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://checkout.paystack.com'),

    /*
    |--------------------------------------------------------------------------
    | Webhook URL
    |--------------------------------------------------------------------------
    |
    | The URL that Paystack will call to notify you of events.
    |
    */

    'webhook_url' => env('PAYSTACK_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Secret key for verifying webhook signatures.
    |
    */

    'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | Default currency code in ISO 4217 format.
    | Supported: NGN, USD, GHS, ZAR, KES, XOF
    |
    */

    'currency' => env('PAYSTACK_CURRENCY', 'NGN'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */

    'timeout' => env('PAYSTACK_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    |
    | Additional options for the HTTP client.
    |
    */

    'http' => [
        'verify' => env('PAYSTACK_VERIFY_SSL', true),
        'connect_timeout' => env('PAYSTACK_CONNECT_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable/disable logging of API requests and responses.
    |
    */

    'logging' => [
        'enabled' => env('PAYSTACK_LOGGING_ENABLED', false),
        'channel' => env('PAYSTACK_LOGGING_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Cache configuration for API responses that can be cached.
    |
    */

    'cache' => [
        'enabled' => env('PAYSTACK_CACHE_ENABLED', true),
        'ttl' => env('PAYSTACK_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('PAYSTACK_CACHE_PREFIX', 'paystack'),
    ],
];