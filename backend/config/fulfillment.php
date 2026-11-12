<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Digital code keyring (PROJECT_ARCHITECTURE.md section 10, ADR-010)
    |--------------------------------------------------------------------------
    |
    | Separate keys from APP_KEY. `code_keys` is a comma-separated list of
    | `kid:base64key` pairs, each key 32 bytes for AES-256-GCM. New codes use
    | `active_kid`; older kids stay readable until rotation has re-encrypted
    | their rows. The blind index key builds code_hash.
    |
    | Owner decision 11: the three values are the database settings
    | digital_codes.code_keys, .active_kid and .blind_index_key, the keys
    | envelope-encrypted with APP_KEY, applied here by SettingsOverlay. They are
    | never edited in the admin: `codes:generate-keys` (first install),
    | `codes:rotate-key` and `codes:rebuild-blind-index` (SECURITY.md 5).
    | Empty here: without the settings no code is stored or read.
    |
    */
    'code_keys' => null,
    'active_kid' => null,
    'blind_index_key' => null,

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
