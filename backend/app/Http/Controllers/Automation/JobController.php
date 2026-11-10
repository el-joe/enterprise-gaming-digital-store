<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Enums\AutomationTask;
use App\Domains\Automation\Services\AutomationJobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tasks n8n may start when it is their driver (ADR-014). Laravel does the work.
 */
class JobController extends AutomationController
{
    public function __construct(private AutomationJobService $jobs) {}

    public function catalogSync(Request $request): JsonResponse
    {
        return $this->run($request, AutomationTask::CatalogSync);
    }

    public function exchangeRates(Request $request): JsonResponse
    {
        return $this->run($request, AutomationTask::ExchangeRates);
    }

    public function reconcilePayments(Request $request): JsonResponse
    {
        return $this->run($request, AutomationTask::PaymentReconciliation);
    }

    private function run(Request $request, AutomationTask $task): JsonResponse
    {
        $result = $this->jobs->run($this->caller($request), $task);

        return $this->success($result, $result['repeated'] ? 'Already queued for this execution.' : 'Queued.', status: $result['repeated'] ? 200 : 202);
    }
}
