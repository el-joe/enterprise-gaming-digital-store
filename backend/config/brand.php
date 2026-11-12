<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand identity
    |--------------------------------------------------------------------------
    |
    | Single source for the store's name and logo files. Logo files live in
    | public/images/brand and are generated from the masters in
    | resources/assets/images/brand (see docs/ASSETS.md for the pipeline).
    | Widths are the intrinsic pixel widths of each exported file.
    |
    */

    // Admin setting site.brand_name (owner decision 11), applied by SettingsOverlay.
    'name' => 'boxcards.shop',

    'theme_color' => '#07080B',

    'logo' => [
        'path' => 'images/brand/logo-%dw',
        'widths' => [320, 640, 1280],
        'aspect' => [1885, 424],
    ],

    'mark' => [
        'path' => 'images/brand/mark-%d',
        'widths' => [64, 128, 256],
    ],

    'email_logo' => [
        'path' => 'images/brand/logo-email.png',
        'width' => 240,
        'height' => 60,
    ],

    'og_image' => 'images/brand/og-default.jpg',

];
