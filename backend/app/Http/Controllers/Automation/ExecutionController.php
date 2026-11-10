<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Services\AutomationAlertService;
use App\Domains\Automation\Services\AutomationExecutionService;
use App\Http\Requests\Automation\AlertRequest;
use App\Http\Requests\Automation\ExecutionRequest;
use Illuminate\Http\JsonResponse;

/**
 * Execution log with duplicate-execution protection, and alerts from the error workflow.
 */
class ExecutionController extends AutomationController
{
    public function __construct(private AutomationExecutionService $executions, private AutomationAlertService $alerts) {}

    public function store(ExecutionRequest $request): JsonResponse
    {
        $caller = $this->caller($request);

        if ($request->validated('action') === 'start') {
            $result = $this->executions->start($caller);

            return $this->success($result, $result['duplicate'] ? 'Duplicate execution; stop.' : 'Execution started.', status: $result['duplicate'] ? 200 : 201);
        }

        return $this->success($this->executions->finish(
            $caller,
            $request->validated('status') === 'success',
            $request->validated('summary'),
            $request->validated('error'),
        ), 'Execution finished.');
    }

    public function alert(AlertRequest $request): JsonResponse
    {
        /** @var array{severity: string, title: string} $alert */
        $alert = $request->validated();

        return $this->success($this->alerts->record($this->caller($request), $alert), 'Alert recorded.', status: 201);
    }
}
