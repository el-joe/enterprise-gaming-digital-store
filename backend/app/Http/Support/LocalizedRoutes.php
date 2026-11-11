<?php

namespace App\Http\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Storefront page URLs per language (SEO_STRATEGY.md section 2): the default language is
 * unprefixed (`storefront.home` is `/`), every other supported language is served under
 * `/{locale}` with the route name prefixed by `{locale}.` (`ar.storefront.home` is `/ar`).
 */
final class LocalizedRoutes
{
    /**
     * The route name of a storefront page in a language (the current one by default).
     */
    public static function name(string $name, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === self::defaultLocale() ? $name : $locale.'.'.$name;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function url(string $name, array $parameters = [], ?string $locale = null): string
    {
        return route(self::name($name, $locale), $parameters);
    }

    /**
     * The same page in another language, keeping route parameters and the query string. Pages
     * that have no localized twin fall back to that language's home page.
     */
    public static function switchUrl(Request $request, string $locale): string
    {
        $route = $request->route();
        $base = $route instanceof Route ? self::baseName((string) $route->getName()) : null;

        if ($base === null || ! app('router')->has(self::name($base, $locale))) {
            return self::url('storefront.home', locale: $locale);
        }

        $url = route(self::name($base, $locale), $route->parameters());
        $query = $request->getQueryString();

        return $query === null ? $url : $url.'?'.$query;
    }

    /**
     * Hreflang alternates of a canonical page (SEO_STRATEGY.md section 6, M18): the same route
     * in every supported language, carrying only the canonical URL's query string (at most
     * `page`), so filters, sort, tracking and currency never reach an alternate. Empty when the
     * page is not its own canonical (an override elsewhere) or has no localized twin.
     *
     * @return array<string, string> locale => URL
     */
    public static function alternates(Request $request, string $canonical): array
    {
        $route = $request->route();
        $base = $route instanceof Route ? self::baseName((string) $route->getName()) : null;

        if ($base === null) {
            return [];
        }

        $current = route((string) $route->getName(), $route->parameters());
        $canonicalPath = explode('?', $canonical, 2)[0];

        if (rtrim($canonicalPath, '/') !== rtrim($current, '/')) {
            return [];
        }

        $query = parse_url($canonical, PHP_URL_QUERY);
        $alternates = [];

        foreach ((array) config('app.supported_locales') as $locale) {
            if (app('router')->has(self::name($base, $locale))) {
                $alternates[$locale] = route(self::name($base, $locale), $route->parameters()).(is_string($query) && $query !== '' ? '?'.$query : '');
            }
        }

        return count($alternates) > 1 ? $alternates : [];
    }

    /**
     * Supported languages other than the default one, each served under its own prefix.
     *
     * @return list<string>
     */
    public static function prefixedLocales(): array
    {
        return array_values(array_filter(
            (array) config('app.supported_locales'),
            static fn (string $locale): bool => $locale !== self::defaultLocale(),
        ));
    }

    /**
     * The language a path belongs to (used where no route matched, such as the 404 page).
     */
    public static function localeOfPath(string $path): string
    {
        $first = explode('/', trim($path, '/'))[0];

        return in_array($first, self::prefixedLocales(), true) ? $first : self::defaultLocale();
    }

    public static function defaultLocale(): string
    {
        return (string) config('app.fallback_locale');
    }

    private static function baseName(string $name): ?string
    {
        foreach (self::prefixedLocales() as $locale) {
            if (str_starts_with($name, $locale.'.')) {
                $name = substr($name, strlen($locale) + 1);
            }
        }

        return str_starts_with($name, 'storefront.') ? $name : null;
    }
}
