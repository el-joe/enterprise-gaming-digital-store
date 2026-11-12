<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Support\LocalizedRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Storefront v1 catalog URLs, moved for good by Storefront v2 (301, query string kept):
 * `/gaming` to `/products`, `/gaming/{slug}` to `/b/{slug}` and a top-level category
 * `/{slug}` to `/c/{slug}` (an unknown slug stays a 404, so the M18 redirects runtime and the
 * 404 log still see it). The same for every language prefix.
 */
class LegacyCatalogRedirectController extends Controller
{
    public function platforms(Request $request): RedirectResponse
    {
        return $this->moved($request, LocalizedRoutes::url('storefront.products.index'));
    }

    public function platform(Request $request, string $slug): RedirectResponse
    {
        return $this->moved($request, LocalizedRoutes::url('storefront.brand', ['slug' => $slug]));
    }

    public function category(Request $request, string $slug): RedirectResponse
    {
        if (! Category::query()->where('slug', $slug)->where('is_active', true)->exists()) {
            throw new NotFoundHttpException;
        }

        return $this->moved($request, LocalizedRoutes::url('storefront.category', ['slug' => $slug]));
    }

    private function moved(Request $request, string $target): RedirectResponse
    {
        $query = $request->getQueryString();

        return redirect()->to($query === null ? $target : $target.'?'.$query, 301);
    }
}
