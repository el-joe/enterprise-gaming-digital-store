<?php

namespace App\Http\Middleware\Admin;

use App\Domains\Identity\Services\TwoFactorService;
use App\Domains\Shared\Exceptions\ApplicationException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactor
{
    /**
     * Require confirmed TOTP and a session that actually passed the challenge.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->hasTwoFactorEnabled() || ! $request->session()->has(TwoFactorService::SESSION_PASSED_KEY)) {
            throw new ApplicationException('Two-factor authentication is required.', 'two_factor_required', 403);
        }

        return $next($request);
    }
}
