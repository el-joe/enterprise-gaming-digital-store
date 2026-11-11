<?php

namespace App\Http\Controllers\Admin\Automation;

use App\Domains\Automation\Services\AutomationWorkflowRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Enable or disable one n8n workflow (M15; the `automation:workflows` command on a screen).
 * Only enabled workflows may call the automation API. Audited.
 */
class AutomationWorkflowController extends Controller
{
    public function __construct(private AutomationWorkflowRegistry $workflows) {}

    public function enable(string $workflow): JsonResponse
    {
        $this->workflows->setEnabled([$workflow], true);

        return $this->success(message: 'Workflow enabled.');
    }

    public function disable(string $workflow): JsonResponse
    {
        $this->workflows->setEnabled([$workflow], false);

        return $this->success(message: 'Workflow disabled.');
    }
}
