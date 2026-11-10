<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\Enums\AutomationLogKind;
use App\Domains\Automation\Enums\AutomationLogStatus;
use App\Domains\Automation\Models\AutomationLog;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Services\SensitiveDataRedactor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * n8n execution log and duplicate-execution protection (N8N_AUTOMATIONS.md section 2). An
 * execution is identified by workflow id and execution key, unique in automation_logs.
 *
 * - The first start creates the row (`started`).
 * - A start for a key that is `started` or `success` returns `skipped_duplicate`; the workflow
 *   must stop.
 * - A start for a key whose earlier run `failed` restarts it (the attempt count is kept).
 * - Finish moves `started` to `success` or `failed` once.
 */
class AutomationExecutionService
{
    private const int SUMMARY_MAX_BYTES = 16384;

    public function __construct(private AutomationWorkflowRegistry $workflows, private SensitiveDataRedactor $redactor) {}

    /**
     * @return array{status: string, duplicate: bool, workflow_id: string, execution_key: string, attempt: int, started_at: string}
     */
    public function start(AutomationCaller $caller): array
    {
        try {
            $log = DB::transaction(function () use ($caller): AutomationLog {
                $existing = $this->executions($caller)->lockForUpdate()->first();

                if ($existing === null) {
                    $log = new AutomationLog;
                    $log->forceFill([
                        'kind' => AutomationLogKind::Execution,
                        'service_account_id' => $caller->account->id,
                        'workflow_id' => $caller->workflowId,
                        'execution_key' => $caller->executionKey,
                        'status' => AutomationLogStatus::Started,
                        'started_at' => now(),
                        'summary' => ['attempt' => 1],
                    ])->save();

                    return $log;
                }

                if ($existing->status === AutomationLogStatus::Failed) {
                    $existing->forceFill([
                        'status' => AutomationLogStatus::Started,
                        'started_at' => now(),
                        'finished_at' => null,
                        'error' => null,
                        'summary' => ['attempt' => (int) ($existing->summary['attempt'] ?? 1) + 1],
                    ])->save();

                    return $existing;
                }

                $existing->setAttribute('duplicate', true);

                return $existing;
            });
        } catch (UniqueConstraintViolationException) {
            $log = $this->executions($caller)->firstOrFail();
            $log->setAttribute('duplicate', true);
        }

        $duplicate = (bool) $log->getAttribute('duplicate');

        return [
            'status' => $duplicate ? AutomationLogStatus::SkippedDuplicate->value : AutomationLogStatus::Started->value,
            'duplicate' => $duplicate,
            'workflow_id' => $caller->workflowId,
            'execution_key' => $caller->executionKey,
            'attempt' => (int) ($log->summary['attempt'] ?? 1),
            'started_at' => $log->started_at->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $summary
     * @return array{status: string, workflow_id: string, execution_key: string, finished_at: string}
     */
    public function finish(AutomationCaller $caller, bool $succeeded, ?array $summary, ?string $error): array
    {
        $log = DB::transaction(function () use ($caller, $succeeded, $summary, $error): AutomationLog {
            $log = $this->executions($caller)->lockForUpdate()->first();

            if ($log === null || $log->status !== AutomationLogStatus::Started) {
                throw new ApplicationException('This execution is not running.', 'execution_not_started', 409);
            }

            $log->forceFill([
                'status' => $succeeded ? AutomationLogStatus::Success : AutomationLogStatus::Failed,
                'finished_at' => now(),
                'summary' => $this->summary([...($log->summary ?? []), ...($summary === null ? [] : ['result' => $summary])]),
                'error' => $error === null ? null : mb_substr($error, 0, 2000),
            ])->save();

            return $log;
        });

        $this->workflows->recordOutcome($caller->workflowId, $succeeded);

        return [
            'status' => $log->status->value,
            'workflow_id' => $caller->workflowId,
            'execution_key' => $caller->executionKey,
            'finished_at' => $log->finished_at->toIso8601String(),
        ];
    }

    /**
     * The caller's execution, which must be running; used by endpoints that start work so that a
     * duplicate or finished execution cannot repeat it. Locked when called inside a transaction.
     */
    public function lockRunning(AutomationCaller $caller): AutomationLog
    {
        $log = $this->executions($caller)->lockForUpdate()->first();

        if ($log === null || $log->status !== AutomationLogStatus::Started) {
            throw new ApplicationException('Start this execution first (POST /executions); work is only done for a running execution.', 'execution_not_started', 409);
        }

        return $log;
    }

    /**
     * Mark an execution failed from the error workflow. The n8n error trigger does not know the
     * failed run's execution key, so without one the newest running execution of the workflow
     * started in the last 24 hours is closed. Unknown or finished executions are left as they are.
     */
    public function failFromAlert(string $workflowId, ?string $executionKey, string $error): bool
    {
        $running = AutomationLog::query()
            ->where('kind', AutomationLogKind::Execution)
            ->where('workflow_id', $workflowId)
            ->where('status', AutomationLogStatus::Started);

        $id = $executionKey !== null
            ? (clone $running)->where('execution_key', $executionKey)->value('id')
            : (clone $running)->where('started_at', '>=', now()->subDay())->orderByDesc('started_at')->orderByDesc('id')->value('id');

        $updated = $id === null ? 0 : AutomationLog::query()->whereKey($id)->where('status', AutomationLogStatus::Started)
            ->update(['status' => AutomationLogStatus::Failed, 'finished_at' => now(), 'error' => mb_substr($error, 0, 2000)]);

        return $updated > 0;
    }

    /**
     * @return Builder<AutomationLog>
     */
    private function executions(AutomationCaller $caller): Builder
    {
        return AutomationLog::query()
            ->where('kind', AutomationLogKind::Execution)
            ->where('workflow_id', $caller->workflowId)
            ->where('execution_key', $caller->executionKey);
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return array<string, mixed>
     */
    private function summary(array $summary): array
    {
        $summary = $this->redactor->redact($summary);

        if (strlen((string) json_encode($summary)) > self::SUMMARY_MAX_BYTES) {
            return ['attempt' => $summary['attempt'] ?? 1, 'truncated' => true];
        }

        return $summary;
    }
}
