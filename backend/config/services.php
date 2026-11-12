<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => null, // Postmark transport not installed.
    ],

    'resend' => [
        'key' => null, // Resend transport not installed.
    ],

    'ses' => [
        // SES needs the AWS SDK (not installed); mail uses the mail settings group.
        'key' => null,
        'secret' => null,
        'region' => 'us-east-1',
    ],

    // Which exchange rate source currency:fetch-rates uses: currency_api (free, daily, no key;
    // default) or open_exchange_rates (needs an app id). Admin setting currency.rates_provider.
    'exchange_rates' => [
        'provider' => 'currency_api',
    ],

    'currency_api' => [
        // Tried in order; the second is the project's official mirror.
        'urls' => [
            'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/{base}.json',
            'https://latest.currency-api.pages.dev/v1/currencies/{base}.json',
        ],
        'connect_timeout' => 5,
        'timeout' => 15,
        'max_staleness_days' => 3,
    ],

    'open_exchange_rates' => [
        // Admin setting currency.oxr_app_id (encrypted), applied by SettingsOverlay.
        'app_id' => null,
        // Admin settings currency.oxr_base_url, oxr_connect_timeout, oxr_timeout (owner decision 11).
        'base_url' => 'https://openexchangerates.org/api/',
        'connect_timeout' => 5,
        'timeout' => 15,
    ],

    'slack' => [
        'notifications' => [
            // Slack notification channel not installed.
            'bot_user_oauth_token' => null,
            'channel' => null,
        ],
    ],

];
