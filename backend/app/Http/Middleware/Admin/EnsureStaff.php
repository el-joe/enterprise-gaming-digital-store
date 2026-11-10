<?php

namespace App\Http\Middleware\Admin;

use App\Domains\Identity\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureStaff
{
    /**
     * Allow only active staff accounts through.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isStaff() || $user->status !== UserStatus::Active) {
            throw new AccessDeniedHttpException;
        }

        return $next($request);
    }
}
