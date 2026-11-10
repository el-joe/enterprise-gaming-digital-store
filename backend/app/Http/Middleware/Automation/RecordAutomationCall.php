<?php

namespace App\Http\Middleware\Automation;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\Services\AutomationCallRecorder;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Outermost automation middleware: every call, including unauthenticated, badly signed, replayed,
 * forbidden, invalid and rate-limited ones, writes one automation_logs row of kind `call`.
 * Responses are never cached.
 */
class RecordAutomationCall
{
    public function __construct(private AutomationCallRecorder $recorder) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $started = now();
        $clock = hrtime(true);

        $response = $next($request);

        $caller = $request->attributes->get(AutomationCaller::REQUEST_ATTRIBUTE);

        $this->recorder->record(
            $caller instanceof AutomationCaller ? $caller : null,
            (string) $request->header(AuthenticateAutomationRequest::WORKFLOW_HEADER),
            (string) $request->header(AuthenticateAutomationRequest::EXECUTION_HEADER),
            Str::after((string) $request->route()?->getName(), 'automation.'),
            $request->getMethod(),
            $request->query(),
            $response->getStatusCode(),
            $this->errorCode($response),
            $started,
            intdiv(hrtime(true) - $clock, 1_000_000),
            $request->ip(),
        );

        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }

    private function errorCode(Response $response): ?string
    {
        if ($response->getStatusCode() < 400 || ! $response instanceof JsonResponse) {
            return null;
        }

        $code = $response->getData(true)['code'] ?? null;

        return is_string($code) ? $code : null;
    }
}
