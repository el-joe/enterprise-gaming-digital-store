<?php

use App\Providers\AppServiceProvider;
use App\Providers\SettingsServiceProvider;
use App\Providers\StorefrontServiceProvider;

return [
    SettingsServiceProvider::class,
    AppServiceProvider::class,
    StorefrontServiceProvider::class,
];
