<?php

return [
    'app_key' => env('ALIEXPRESS_APP_KEY'),
    'app_secret' => env('ALIEXPRESS_APP_SECRET'),
    'redirect_uri' => env('ALIEXPRESS_REDIRECT_URI'),
    'authorize_url' => env('ALIEXPRESS_AUTHORIZE_URL', 'https://api-sg.aliexpress.com/oauth/authorize'),
    'sync_base_url' => env('ALIEXPRESS_SYNC_BASE_URL', 'https://api-sg.aliexpress.com/sync'),
    'rest_base_url' => env('ALIEXPRESS_REST_BASE_URL', 'https://api-sg.aliexpress.com/rest'),
    'default_country' => env('ALIEXPRESS_DEFAULT_COUNTRY', 'US'),
    'default_currency' => env('ALIEXPRESS_DEFAULT_CURRENCY', 'USD'),
    'default_language' => env('ALIEXPRESS_DEFAULT_LANGUAGE', 'EN'),
    'default_margin' => (float) env('ALIEXPRESS_DEFAULT_MARGIN', 0),
];
