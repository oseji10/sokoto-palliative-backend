<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://portal.sokotopalliativeshop.com.ng',
        'http://localhost:3000',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],
    'exposed_headers' => [],
    'max_age' => 1728000,
    'supports_credentials' => true,
];