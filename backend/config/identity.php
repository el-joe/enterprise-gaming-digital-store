<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Super Admin bootstrap account
    |--------------------------------------------------------------------------
    |
    | Read only by SuperAdminSeeder, which refuses to run when either is empty.
    |
    */

    'super_admin' => [
        'email' => env('SUPER_ADMIN_EMAIL'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin idle timeout
    |--------------------------------------------------------------------------
    |
    | Admin setting security.admin_idle_minutes (SettingsOverlay); 30 is the built-in default.
    |
    */

    'admin_idle_minutes' => 30,

];
