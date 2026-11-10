<?php

namespace App\Http\Middleware\Storefront;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Short public caching for anonymous catalog and config GETs (M-A4): `Cache-Control: public,
 * max-age=N`, an ETag of the body (304 on a matching `If-None-Match`) and `Vary` on the request
 * headers that change the body. `currency` adds `X-Currency` and `Cookie` (the web display
 * currency cookie) to `Vary`. Only for routes whose response is identical for every caller
 * with the same URL and varying headers; never for anything personal.
 */
class PublicCacheHeaders
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $maxAge = '60', string $varies = ''): Response
    {
        $response = $next($request);

        if (! $request->isMethodCacheable() || ! $response->isSuccessful()) {
            return $response;
        }

        $vary = ['Accept-Language'];

        if ($varies === 'currency') {
            array_push($vary, 'X-Currency', 'Cookie');
        }

        $response->headers->set('Cache-Control', 'public, max-age='.max(0, (int) $maxAge));
        $response->headers->set('Vary', implode(', ', $vary));

        $content = $response->getContent();

        if (is_string($content) && $content !== '') {
            $response->setEtag(hash('xxh128', $content));
            $response->isNotModified($request);
        }

        return $response;
    }
}
