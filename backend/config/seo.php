<?php

/*
|--------------------------------------------------------------------------
| SEO (M18, docs/SEO_STRATEGY.md)
|--------------------------------------------------------------------------
*/

return [

    /*
    | Sitemaps (strategy section 5): `seo:generate-sitemaps` writes an index and one child per
    | type and language to this disk and directory; `/sitemap.xml` and `/sitemaps/{file}` serve
    | them (generated on the first request when missing). At most `chunk_size` URLs per file.
    */
    'sitemaps' => [
        'disk' => env('SEO_SITEMAP_DISK', 'local'),
        'directory' => 'sitemaps',
        'chunk_size' => 50000,
        'max_age_seconds' => 3600,
    ],

    /*
    | robots.txt (strategy section 5). `block_all` answers `Disallow: /` (staging and preview
    | hosts that must never be indexed).
    */
    'robots' => [
        'block_all' => (bool) env('SEO_BLOCK_ALL_CRAWLERS', false),
        'disallow' => ['/admin', '/checkout', '/cart', '/account', '/api', '/search', '/orders'],
    ],

    /*
    | Redirects runtime (strategy section 7): cached lookups after a route miss, at most
    | `max_hops` followed when a chain slipped through, and the first path segments that are
    | never redirected, normalised or logged (JSON, admin, webhooks, app links, payment returns, files).
    */
    'redirects' => [
        'cache_seconds' => 3600,
        'max_hops' => 5,
        'excluded_prefixes' => ['api', 'admin', 'webhooks', 'app', 'checkout', 'storage', 'build', 'up', 'sanctum', '.well-known'],
    ],

    /*
    | Structured data (strategy section 4). Days a rendered price is declared valid
    | (`priceValidUntil`); prices follow exchange rates, so this stays short.
    */
    'structured_data' => [
        'price_valid_days' => 7,
    ],
];
