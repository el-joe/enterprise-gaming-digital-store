<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Digital code keyring (PROJECT_ARCHITECTURE.md section 10, ADR-010)
    |--------------------------------------------------------------------------
    |
    | Separate from APP_KEY. DIGITAL_CODE_KEYS is a comma-separated list of
    | `kid:base64key` pairs, each key 32 bytes for AES-256-GCM. New codes use
    | DIGITAL_CODE_ACTIVE_KID; older kids stay readable until rotation has
    | re-encrypted their rows. The blind index key builds code_hash.
    |
    */
    'code_keys' => env('DIGITAL_CODE_KEYS'),
    'active_kid' => env('DIGITAL_CODE_ACTIVE_KID'),
    'blind_index_key' => env('DIGITAL_CODE_BLIND_INDEX_KEY'),

    /*
    | Customer reveal needs a password confirmation this recent; guests need the
    | signed link (valid this many hours) and an email one-time code.
    */
    'reveal_recent_auth_minutes' => 15,
    'guest_link_hours' => 72,
    'reveal_per_hour' => 10,

    /*
    | Web code reveal (M13, owner decision 7): a revealed code is hidden again after
    | this many seconds (and at once when the tab is hidden), and a copied code is
    | cleared from the clipboard after the same time where the browser allows it.
    */

    'web_reveal_hide_seconds' => 60,

    'stock_import_max_codes' => 1000,
];
