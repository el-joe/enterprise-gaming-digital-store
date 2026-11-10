<?php

namespace App\Http\Middleware\Storefront;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * The guest access link from the delivery email is signed for the web route
 * `api.v1.checkout.guest.orders.access`. The web route and its stateless twin under
 * `/api/v1/mobile` (ADR-036) both check the signature and expiry against that canonical URL, so
 * the app can present the same link it opened. Same semantics as the `signed` middleware.
 */
class ValidateGuestAccessSignature
{
    public const string CANONICAL_ROUTE = 'api.v1.checkout.guest.orders.access';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $canonical = route(self::CANONICAL_ROUTE, ['orderPublicId' => (string) $request->route('orderPublicId')]);
        $query = (string) $request->server->get('QUERY_STRING');

        if (! URL::hasValidSignature(Request::create($query === '' ? $canonical : $canonical.'?'.$query))) {
            throw new InvalidSignatureException;
        }

        return $next($request);
    }
}
