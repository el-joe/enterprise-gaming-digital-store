<?php

namespace App\Http\Middleware\Storefront;

use App\Domains\Catalog\Models\Product;
use App\Domains\Seo\Services\NotFoundLogger;
use App\Domains\Seo\Services\RedirectResolver;
use App\Http\Support\LocalizedRoutes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * URL rules of SEO_STRATEGY.md sections 2 and 7 (M18), global, for storefront GET and HEAD
 * requests only (JSON, admin, webhooks and files are excluded by `seo.redirects.excluded_prefixes`):
 *
 * 1. Before routing: a trailing slash is removed with a 301 (one URL per page).
 * 2. After a 404: the admin redirects (301, 302, 410; cached, chains followed safely), then a
 *    permanently removed (soft-deleted) product is 410 Gone, then an upper-case path whose
 *    lower-case form is a storefront page is sent there with a 301; otherwise the miss is counted in `not_found_logs` and the
 *    designed 404 page stays. The query string is kept on redirects whose target has none.
 *
 * There is never a country segment: `/eg/...` is an ordinary 404 (owner decision).
 */
class ResolveSeoUrls
{
    public function __construct(private RedirectResolver $redirects, private NotFoundLogger $notFound) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->applies($request)) {
            return $next($request);
        }

        $path = $request->getPathInfo();

        if ($path !== '/' && str_ends_with($path, '/') && trim($path, '/') !== '') {
            return $this->redirectTo($request, rtrim($path, '/'), 301);
        }

        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $decoded = rawurldecode($path);
        $match = $this->redirects->resolve($decoded);

        if ($match !== null) {
            $this->redirects->countHits($match);

            return $match->isGone() ? $this->gone($decoded) : $this->redirectTo($request, (string) $match->target, $match->status);
        }

        if ($this->isRemovedProduct($decoded)) {
            return $this->gone($decoded);
        }

        $lower = mb_strtolower($decoded);

        if ($lower !== $decoded && $this->isStorefrontPage($lower)) {
            return $this->redirectTo($request, $lower, 301);
        }

        if (! preg_match('~\.[a-z0-9]{2,5}$~i', $decoded)) {
            $this->notFound->record($decoded);
        }

        return $response;
    }

    private function applies(Request $request): bool
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        $first = explode('/', trim($request->getPathInfo(), '/'))[0];

        return ! in_array($first, (array) config('seo.redirects.excluded_prefixes'), true);
    }

    /**
     * A product URL (any language) whose slug only exists on a soft-deleted product.
     */
    private function isRemovedProduct(string $path): bool
    {
        $locales = implode('|', array_map('preg_quote', LocalizedRoutes::prefixedLocales()));

        if (! preg_match('~^/(?:(?:'.$locales.')/)?products/([a-z0-9]+(?:-[a-z0-9]+)*)$~', $path, $match)) {
            return false;
        }

        return Product::onlyTrashed()->where('slug', $match[1])->exists()
            && ! Product::query()->where('slug', $match[1])->exists();
    }

    /**
     * The lower-case path is a storefront page route (never a token, file or app link).
     */
    private function isStorefrontPage(string $path): bool
    {
        try {
            $route = app('router')->getRoutes()->match(Request::create($path));
        } catch (\Throwable) {
            return false;
        }

        return str_contains((string) $route->getName(), 'storefront.');
    }

    private function redirectTo(Request $request, string $target, int $status): Response
    {
        $query = $request->getQueryString();

        if ($query !== null && ! str_contains($target, '?')) {
            $target .= '?'.$query;
        }

        return redirect()->to($target === '' ? '/' : $target, $status === 302 ? 302 : 301);
    }

    private function gone(string $path): Response
    {
        app()->setLocale(LocalizedRoutes::localeOfPath($path));

        return response()->view('errors.410', [], 410);
    }
}
