<?php

return [
    'paths' => ['api/*', 'login', 'logout', '*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://api.makenstream.com',
        'http://localhost:3000' // optional for local testing
    ],

    'allowed_headers' => ['*'],

    'supports_credentials' => true,

    'allowed_origins_patterns' => [],

    'exposed_headers' => [],

    'max_age' => 0,
];
