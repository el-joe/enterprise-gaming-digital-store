<?php

namespace App\Http\Middleware\Automation;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Shared\Exceptions\ApplicationException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `automation.scope:reports:read` requires that exact scope on the token.
 */
class RequireAutomationScope
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $caller = $request->attributes->get(AutomationCaller::REQUEST_ATTRIBUTE);
        $required = AutomationScope::from($scope);

        if (! $caller instanceof AutomationCaller || ! $caller->has($required)) {
            throw new ApplicationException("This token lacks the {$required->value} scope.", 'scope_missing', 403);
        }

        return $next($request);
    }
}
