<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',          // Your Vite development server (adjust port if different)
        'http://localhost:5173',          // Another common dev port
        'http://localhost',
        'http://127.0.0.1:8000',
        'https://onlyren.noupal.pro',     // Your production frontend domain
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // <<< CRUCIAL: Must be true for Sanctum/cookies/Authorization headers
];