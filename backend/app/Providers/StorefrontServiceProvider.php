<?php

namespace App\Providers;

use App\Http\Support\LocalizedRoutes;
use App\View\Composers\AccountLayoutComposer;
use App\View\Composers\StorefrontLayoutComposer;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Storefront pages (M12) and account pages (M13): the layout view composers and `url()->localized()` for links that
 * stay in the page's language.
 */
class StorefrontServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.storefront', StorefrontLayoutComposer::class);
        View::composer('layouts.account', AccountLayoutComposer::class);

        // Registered on the class, so booting never resolves the URL generator (and its request).
        UrlGenerator::macro('localized', function (string $name, array $parameters = []): string {
            /** @var UrlGenerator $this */
            return LocalizedRoutes::url($name, $parameters);
        });
    }
}
