<?php

return [
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'business_shortcode' => env('MPESA_BUSINESS_SHORTCODE'),
    'passkey' => env('MPESA_PASSKEY'),
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
];