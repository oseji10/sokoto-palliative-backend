<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Moniepoint POS Terminal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Moniepoint POS terminal integration
    |
    */

    'base_url' => env('MONIEPOINT_BASE_URL', 'https://api.pos.moniepoint.com'),

    'bearer_token' => env('MONIEPOINT_BEARER_TOKEN'),
];
