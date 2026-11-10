<?php

namespace App\Http\Middleware\Account;

use App\Domains\Identity\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureAccountActive
{
    /**
     * Deny suspended and closed accounts that still hold a session.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->status !== UserStatus::Active) {
            throw new AccessDeniedHttpException;
        }

        return $next($request);
    }
}
