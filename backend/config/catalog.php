<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reserved top-level slugs
    |--------------------------------------------------------------------------
    |
    | Categories live at /{slug}. These names are planned or conventional first path segments.
    | Every first segment of a registered route is reserved automatically on top of this list.
    |
    */
    'reserved_slugs' => [
        'account', 'admin', 'api', 'ar', 'assets', 'auth', 'blog', 'build', 'cart', 'checkout', 'contact',
        'css', 'en', 'favicon', 'forgot-password', 'gaming', 'guest', 'images', 'js', 'login', 'logout',
        'media', 'orders', 'pages', 'password', 'payments', 'products', 'register', 'reset-password',
        'robots', 'search', 'sitemap', 'storage', 'support', 'two-factor-challenge', 'up', 'vendor',
        'webhooks', 'wishlist',
    ],

    'search_locales' => ['en', 'ar'],

    'media' => [
        'disk' => 'public',
        'max_upload_kilobytes' => 8192,
        'max_dimension_pixels' => 8000,
    ],
];
