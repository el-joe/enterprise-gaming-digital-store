<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LogicException;

/**
 * Base for /api/v1/automation controllers. Token, signature, replay and scope checks, rate
 * limiting and the automation_logs call row are route middleware; controllers translate HTTP to
 * the automation services.
 */
abstract class AutomationController extends Controller
{
    protected function caller(Request $request): AutomationCaller
    {
        $caller = $request->attributes->get(AutomationCaller::REQUEST_ATTRIBUTE);

        return $caller instanceof AutomationCaller ? $caller : throw new LogicException('Automation route without automation authentication.');
    }
}
