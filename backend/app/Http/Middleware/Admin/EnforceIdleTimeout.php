<?php

namespace App\Http\Middleware\Admin;

use App\Domains\Identity\Services\AuthenticationService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceIdleTimeout
{
    private const string SESSION_KEY = 'admin.last_activity_at';

    public function __construct(private AuthenticationService $authentication) {}

    /**
     * End an admin session after the configured period of inactivity.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $lastActivity = $session->get(self::SESSION_KEY);
        $limitSeconds = (int) config('identity.admin_idle_minutes') * 60;

        if ($lastActivity !== null && now()->getTimestamp() - $lastActivity > $limitSeconds) {
            $this->authentication->logout($session);

            throw new AuthenticationException;
        }

        $session->put(self::SESSION_KEY, now()->getTimestamp());

        return $next($request);
    }
}
