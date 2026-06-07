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

    'pricing' => [
        'markup_type' => env('ALIEXPRESS_MARKUP_TYPE', 'percentage'),
        'markup_value' => (float) env('ALIEXPRESS_MARKUP_VALUE', env('ALIEXPRESS_DEFAULT_MARGIN', 0)),
        'minimum_profit' => (float) env('ALIEXPRESS_MINIMUM_PROFIT', 0),
        'include_shipping_cost' => (bool) env('ALIEXPRESS_INCLUDE_SHIPPING_COST', false),
        'rounding_rule' => env('ALIEXPRESS_ROUNDING_RULE', 'none'),
        'currency' => env('ALIEXPRESS_PRICING_CURRENCY', env('ALIEXPRESS_DEFAULT_CURRENCY', 'USD')),
        'compare_at_multiplier' => (float) env('ALIEXPRESS_COMPARE_AT_MULTIPLIER', 0),
    ],

    'policy' => [
        'require_variants' => (bool) env('ALIEXPRESS_REQUIRE_VARIANTS', false),
        'minimum_rating' => (float) env('ALIEXPRESS_MINIMUM_RATING', 0),
        'minimum_order_count' => (int) env('ALIEXPRESS_MINIMUM_ORDER_COUNT', 0),
        'blocked_keywords' => array_filter(array_map('trim', explode(',', env('ALIEXPRESS_BLOCKED_KEYWORDS', 'weapon,gun,knife,counterfeit,replica')))),
        'suspicious_brand_terms' => array_filter(array_map('trim', explode(',', env('ALIEXPRESS_SUSPICIOUS_BRAND_TERMS', 'nike,adidas,apple,samsung,louis vuitton,gucci')))),
    ],

    'sync' => [
        'schedule_enabled' => (bool) env('ALIEXPRESS_SYNC_SCHEDULE_ENABLED', false),
        'schedule' => env('ALIEXPRESS_SYNC_SCHEDULE', '0 */6 * * *'),
        'safe_update_price' => (bool) env('ALIEXPRESS_SAFE_UPDATE_PRICE', true),
        'safe_update_stock' => (bool) env('ALIEXPRESS_SAFE_UPDATE_STOCK', true),
    ],

    'catalog' => [
        'search_method' => env('ALIEXPRESS_CATALOG_SEARCH_METHOD', 'aliexpress.ds.text.search'),
        'category_method' => env('ALIEXPRESS_CATEGORY_METHOD', 'aliexpress.ds.category.get'),
        'local' => env('ALIEXPRESS_CATALOG_LOCAL', 'en_US'),
        'cache_ttl' => (int) env('ALIEXPRESS_CATALOG_CACHE_TTL', 900),
        'fallback_categories' => [
            ['id' => '7', 'name' => 'Computer & Office'],
            ['id' => '15', 'name' => 'Home & Garden'],
            ['id' => '18', 'name' => 'Sports & Entertainment'],
            ['id' => '21', 'name' => 'Toys & Hobbies'],
            ['id' => '26', 'name' => 'Watches'],
            ['id' => '30', 'name' => 'Security & Protection'],
            ['id' => '34', 'name' => 'Automobiles & Motorcycles'],
            ['id' => '36', 'name' => 'Consumer Electronics'],
            ['id' => '39', 'name' => 'Lights & Lighting'],
            ['id' => '44', 'name' => 'Phones & Telecommunications'],
            ['id' => '66', 'name' => 'Beauty & Health'],
            ['id' => '1501', 'name' => 'Mother & Kids'],
            ['id' => '1503', 'name' => 'Furniture'],
            ['id' => '1509', 'name' => 'Jewelry & Accessories'],
            ['id' => '1511', 'name' => 'Luggage & Bags'],
            ['id' => '1524', 'name' => 'Men Clothing'],
            ['id' => '200000345', 'name' => 'Women Clothing'],
            ['id' => '200000343', 'name' => 'Shoes'],
            ['id' => '200000297', 'name' => 'Tools'],
            ['id' => '200003590', 'name' => 'Home Improvement'],
        ],
    ],
];
