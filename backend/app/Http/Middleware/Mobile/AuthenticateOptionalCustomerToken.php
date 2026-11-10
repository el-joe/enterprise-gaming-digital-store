<?php

namespace App\Http\Middleware\Mobile;

use App\Domains\Identity\Enums\UserStatus;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Mobile cart and checkout routes (M-A3, ADR-036) serve guests and signed-in customers. Without an
 * `Authorization` header the request is a guest. With one, it must be a valid customer access
 * token (the same rules as `EnsureCustomerToken`), otherwise 401: a bad token never silently
 * falls back to guest. A suspended or closed account gets 403, as on the account routes.
 */
class AuthenticateOptionalCustomerToken
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->headers->get('Authorization') === null) {
            return $next($request);
        }

        $user = Auth::guard('sanctum')->user();

        if (! EnsureCustomerToken::isCustomerToken($user)) {
            throw new AuthenticationException;
        }

        Auth::shouldUse('sanctum');

        if ($user->status !== UserStatus::Active) {
            throw new AccessDeniedHttpException;
        }

        return $next($request);
    }
}
