<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\Enums\AutomationLogKind;
use App\Domains\Automation\Enums\AutomationLogStatus;
use App\Domains\Automation\Models\AutomationLog;
use App\Domains\Shared\Services\SensitiveDataRedactor;
use App\Http\Middleware\Automation\AuthenticateAutomationRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Context;

/**
 * Writes the automation_logs `call` row for one automation API request. For a request that was
 * not authenticated, the workflow and execution headers are kept only when they are well formed;
 * otherwise `-` and the request id are stored. Only the redacted query string is kept, never the
 * body.
 */
class AutomationCallRecorder
{
    public function __construct(private SensitiveDataRedactor $redactor) {}

    /**
     * @param  array<string, mixed>  $query
     */
    public function record(
        ?AutomationCaller $caller,
        string $workflowHeader,
        string $executionHeader,
        string $endpoint,
        string $method,
        array $query,
        int $status,
        ?string $errorCode,
        CarbonInterface $startedAt,
        int $durationMs,
        ?string $ip,
    ): AutomationLog {
        $requestId = Context::get('request_id');

        $log = new AutomationLog;
        $log->forceFill([
            'kind' => AutomationLogKind::Call,
            'service_account_id' => $caller?->account->id,
            'workflow_id' => $caller->workflowId ?? (preg_match(AuthenticateAutomationRequest::WORKFLOW_PATTERN, $workflowHeader) === 1 ? $workflowHeader : '-'),
            'execution_key' => $caller->executionKey ?? (preg_match(AuthenticateAutomationRequest::EXECUTION_KEY_PATTERN, $executionHeader) === 1 ? $executionHeader : (is_string($requestId) ? $requestId : '-')),
            'endpoint' => mb_substr($endpoint === '' ? 'unknown' : $endpoint, 0, 96),
            'status' => $status < 400 ? AutomationLogStatus::Success : AutomationLogStatus::Failed,
            'http_status' => $status,
            'error_code' => $errorCode === null ? null : mb_substr($errorCode, 0, 64),
            'duration_ms' => max(0, $durationMs),
            'ip' => $ip,
            'request_id' => is_string($requestId) ? $requestId : null,
            'started_at' => $startedAt,
            'finished_at' => now(),
            'summary' => ['method' => $method, ...($query === [] ? [] : ['query' => $this->redactor->redact($query)])],
        ])->save();

        return $log;
    }
}
