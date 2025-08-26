<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    | Semua path yang mau di-handle CORS.
    | ['*'] artinya semua route diizinkan.
    */
    'paths' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    | ['*'] artinya semua HTTP method diizinkan (GET, POST, PUT, DELETE, dll).
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    | ['*'] artinya semua domain (origin) diizinkan.
    */
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    | ['*'] artinya semua header request diizinkan.
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    | Header yang di-expose ke browser (biarkan kosong kalau tidak perlu).
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    | Lama (detik) browser boleh cache hasil preflight (OPTIONS).
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    | Kalau butuh kirim cookie/session, set ke true.
    | Kalau tidak perlu, biarkan false.
    */
    'supports_credentials' => false,

];
