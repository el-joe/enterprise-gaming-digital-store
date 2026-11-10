<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Guest checkout on the web (M12)
    |--------------------------------------------------------------------------
    |
    | The owner decided that web checkout requires login (docs/OWNER_DECISIONS.md,
    | decision 4), so the default is off: the checkout page sends guests to sign in
    | or register and back to checkout (the bag is kept and merged on sign-in), and
    | the session place-order route refuses guests with 401 `guest_checkout_disabled`.
    | On: guests check out with an email address, as in the original spec. Guest
    | order access (signed link + email one-time code) works either way.
    |
    */

    'guest_checkout' => (bool) env('STOREFRONT_GUEST_CHECKOUT', false),

    /*
    |--------------------------------------------------------------------------
    | Home hero carousel (M12, owner decision 8)
    |--------------------------------------------------------------------------
    |
    | Seconds between slides when auto-advance is allowed (never with reduced
    | motion, never while the shopper interacts).
    |
    */

    'hero_interval_seconds' => 5,

    /*
    |--------------------------------------------------------------------------
    | Listing page size
    |--------------------------------------------------------------------------
    */

    'per_page' => 24,

];
