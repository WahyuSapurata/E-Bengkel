<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Tentukan path mana saja yang akan di-handle CORS.
    | Contoh: ['api/*'] untuk semua route API.
    |
    */
    'paths' => ['api/*', 'superadmin/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Metode HTTP yang diizinkan. ['*'] artinya semua metode diizinkan.
    |
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Domain yang diizinkan mengakses resource. ['*'] artinya semua.
    | Untuk lebih aman, ganti '*' dengan ['https://adsmotor.id'].
    |
    */
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Header yang diizinkan dalam request.
    |
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Header apa saja yang boleh terlihat oleh browser.
    |
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Waktu dalam detik untuk caching preflight request (OPTIONS).
    |
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Kalau butuh cookie/session, set ke true.
    |
    */
    'supports_credentials' => false,

];
