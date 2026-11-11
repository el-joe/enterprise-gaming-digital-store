<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Language of the admin pages (M14): the choice made with the language switch (session), else
 * the staff member's saved locale, else the default. Admin pages are not indexed, so the
 * language is not part of the URL.
 */
class SetAdminLocale
{
    public const string SESSION_KEY = 'admin.locale';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = (array) config('app.supported_locales');
        $candidates = [$request->session()->get(self::SESSION_KEY), $request->user()?->locale];
        $locale = (string) config('app.fallback_locale');

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                $locale = $candidate;

                break;
            }
        }

        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
