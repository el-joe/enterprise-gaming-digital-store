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
        /*
        | Public origin of media files (a CDN), for example https://cdn.boxcards.shop. Empty: URLs
        | come from the disk (`public`: APP_URL/storage). Storefront APIs always return absolute URLs.
        */
        // Admin setting storage.media_base_url (owner decision 11).
        'base_url' => null,
        'max_upload_kilobytes' => 5120,
        'max_dimension_pixels' => 8000,
    ],
];
