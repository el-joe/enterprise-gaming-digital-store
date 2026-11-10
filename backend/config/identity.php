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
    */

    'admin_idle_minutes' => (int) env('ADMIN_IDLE_MINUTES', 30),

];
