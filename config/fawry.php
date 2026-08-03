<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fawry Pay Credentials (set in .env)
    |--------------------------------------------------------------------------
    | FAWRY_MERCHANT_CODE  – your Fawry merchant code
    | FAWRY_SECURITY_KEY   – your Fawry secure key
    | FAWRY_BASE_URL       – staging: https://atfawry.fawrystaging.com
    |                        production: https://www.atfawry.com
    */
    'merchant_code' => env('FAWRY_MERCHANT_CODE'),
    'security_key' => env('FAWRY_SECURITY_KEY'),
    'base_url' => env('FAWRY_BASE_URL', 'https://atfawry.fawrystaging.com'),
    'currency' => env('FAWRY_CURRENCY', 'EGP'),
    'expiry_hours' => (int) env('FAWRY_EXPIRY_HOURS', 72),
];
