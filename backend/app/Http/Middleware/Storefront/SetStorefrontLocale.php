<?php

namespace App\Http\Middleware\Storefront;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Language of a storefront page (M12). Pages are localized by URL (SEO_STRATEGY.md section 2):
 * the route group passes its language, which is also remembered in the session. Pages without
 * a localized URL (the payment return page, the email reset link) pass `session` and use the
 * language the shopper last browsed in.
 */
class SetStorefrontLocale
{
    public const string SESSION_KEY = 'storefront.locale';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $locale = 'session'): Response
    {
        $supported = (array) config('app.supported_locales');

        if (in_array($locale, $supported, true)) {
            $request->session()->put(self::SESSION_KEY, $locale);
        } else {
            $remembered = $request->session()->get(self::SESSION_KEY);
            $locale = in_array($remembered, $supported, true) ? $remembered : (string) config('app.fallback_locale');
        }

        app()->setLocale($locale);

        $response = $next($request);

        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
