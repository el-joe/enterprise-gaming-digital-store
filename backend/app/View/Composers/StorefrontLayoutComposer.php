<?php

namespace App\View\Composers;

use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Catalog\Services\StorefrontNavigationService;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Services\CartService;
use App\Http\Support\LocalizedRoutes;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data of the storefront layout (header, footer, switchers) for every page that extends it,
 * including the 404 page, which renders without the `web` middleware when no route matched
 * (no session: the bag shows empty and the language comes from the path).
 */
class StorefrontLayoutComposer
{
    public function __construct(
        private Request $request,
        private StorefrontCatalogService $catalog,
        private CartService $carts,
        private StorefrontRequest $storefront,
        private StorefrontNavigationService $navigation,
    ) {}

    public function compose(View $view): void
    {
        if ($this->request->route() === null) {
            app()->setLocale(LocalizedRoutes::localeOfPath($this->request->path()));
        }

        $locale = app()->getLocale();
        $user = $this->request->hasSession() ? $this->request->user() : null;
        $owner = $this->request->hasSession() ? $this->storefront->ownerOrNull($this->request) : null;

        $view->with('storefront', [
            'locale' => $locale,
            'dir' => $locale === 'ar' ? 'rtl' : 'ltr',
            'languages' => array_map(fn (string $code): array => [
                'code' => $code,
                'url' => LocalizedRoutes::switchUrl($this->request, $code),
                'current' => $code === $locale,
            ], (array) config('app.supported_locales')),
            'currencies' => $this->catalog->currencies(),
            'currency' => $this->storefront->currency($this->request),
            'bag_count' => $owner === null ? 0 : $this->carts->unitCount($owner),
            'user' => $user instanceof User ? ['name' => $user->name, 'email_verified' => $user->hasVerifiedEmail()] : null,
            // Storefront v2: categories, top brands and featured header categories (cached).
            'navigation' => $this->navigation->build(),
        ]);
    }
}
