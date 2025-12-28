<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://mendaur.up.railway.app',              // Backend Production
        'https://sedulurmendaur-production.up.railway.app', // Frontend Production
        'http://localhost:5173',                        // Local Vite dev
        'http://127.0.0.1:5173',                        // Local IP Vite dev
        'http://localhost:3000',                        // Alternative local
        'http://127.0.0.1:3000',                        // Alternative local IP
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
