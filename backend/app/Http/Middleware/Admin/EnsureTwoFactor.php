<?php

namespace App\Http\Middleware\Admin;

use App\Domains\Identity\Services\AuthenticationService;
use App\Domains\Identity\Services\TwoFactorService;
use App\Domains\Shared\Exceptions\ApplicationException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactor
{
    public function __construct(private AuthenticationService $authentication) {}

    /**
     * Require confirmed TOTP and a session that actually passed the challenge. With `redirect`
     * (admin pages, M14) a browser is sent to set up two-step verification, or signed out and
     * sent to the admin sign-in when the session never passed the challenge; JSON callers keep
     * the 403 envelope.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $mode = 'json'): Response
    {
        $enabled = $request->user()->hasTwoFactorEnabled();

        if ($enabled && $request->session()->has(TwoFactorService::SESSION_PASSED_KEY)) {
            return $next($request);
        }

        if ($mode === 'redirect' && ! $request->expectsJson()) {
            if (! $enabled) {
                return redirect()->route('admin.two-factor.setup');
            }

            $this->authentication->logout($request->session());

            return redirect()->route('admin.login');
        }

        throw new ApplicationException('Two-factor authentication is required.', 'two_factor_required', 403);
    }
}
