<?php

namespace App\Http\Middleware\Mobile;

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\MobileTokenService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bearer-only gate for /api/v1/mobile routes, after `auth:sanctum`. The request must carry a
 * Sanctum personal access token (not a session) of a customer, holding exactly the `customer`
 * ability; a `*` ability grants nothing. Service-account tokens never get this far because the
 * `sanctum` guard is bound to the `users` provider (config/auth.php).
 */
class EnsureCustomerToken
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $user instanceof User ? $user->currentAccessToken() : null;

        if (! $token instanceof PersonalAccessToken || $user->isStaff() || ! in_array(MobileTokenService::ABILITY, $token->abilities ?? [], true)) {
            throw new AuthenticationException;
        }

        return $next($request);
    }
}
