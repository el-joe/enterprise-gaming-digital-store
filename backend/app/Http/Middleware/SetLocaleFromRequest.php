<?php

namespace App\Http\Middleware;

use App\Domains\Identity\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale of JSON API requests (M-A3, gap G3). Supported locales are `app.supported_locales`
 * (`en`, `ar`); anything else falls back to
 * `app.fallback_locale` (`en`; `app.locale` is overwritten by `setLocale`).
 *
 * Order (ADR-036):
 * - a request with a session (the web): the signed-in customer's saved `users.locale`, then
 *   `Accept-Language`, then the default;
 * - a stateless request (the app and other API clients): `Accept-Language`, then the default.
 *   The app always sends the language it displays; a saved locale is not consulted because the
 *   bearer token is authenticated after this middleware.
 *
 * `Content-Language` is set on the response.
 */
class SetLocaleFromRequest
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolve($request));

        $response = $next($request);
        $response->headers->set('Content-Language', app()->getLocale());

        return $response;
    }

    private function resolve(Request $request): string
    {
        $header = $this->fromHeader($request);

        if ($request->hasSession()) {
            $user = $request->user();
            $saved = $user instanceof User ? $this->supported($user->locale) : null;

            return $saved ?? $header ?? (string) config('app.fallback_locale');
        }

        return $header ?? (string) config('app.fallback_locale');
    }

    /**
     * The first supported language in `Accept-Language`, by quality (`ar-EG` matches `ar`).
     */
    private function fromHeader(Request $request): ?string
    {
        if ($request->headers->get('Accept-Language') === null) {
            return null;
        }

        foreach ($request->getLanguages() as $language) {
            $supported = $this->supported($language);

            if ($supported !== null) {
                return $supported;
            }
        }

        return null;
    }

    private function supported(?string $locale): ?string
    {
        if ($locale === null || $locale === '') {
            return null;
        }

        $primary = strtolower(strtok(str_replace('-', '_', $locale), '_') ?: '');

        return in_array($primary, config('app.supported_locales'), true) ? $primary : null;
    }
}
