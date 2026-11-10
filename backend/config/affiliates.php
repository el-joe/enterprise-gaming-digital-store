<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Affiliate attribution (M11)
    |--------------------------------------------------------------------------
    |
    | A click on an affiliate link stores a random visitor token in an encrypted,
    | HTTP-only cookie and a hash of it in `affiliate_referrals`. An order placed
    | within `attribution_days` of the last click from that browser is attributed
    | to it (last click wins). Self-referrals are never attributed.
    |
    */
    'cookie' => 'aff_ref',
    'attribution_days' => (int) env('AFFILIATE_ATTRIBUTION_DAYS', 30),
];
