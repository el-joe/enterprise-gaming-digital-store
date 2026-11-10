<?php

namespace App\Http\Controllers\Admin\Mcp;

use App\Domains\Mcp\Models\McpPendingAction;
use App\Domains\Mcp\Repositories\McpPendingActionRepository;
use App\Domains\Mcp\Services\McpPendingActionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Mcp\DecideMcpActionRequest;
use App\Http\Requests\Admin\Mcp\ListMcpActionsRequest;
use App\Http\Resources\Mcp\McpAdminActionResource;
use Illuminate\Http\JsonResponse;

/**
 * The out-of-band approval queue for MCP write actions (`mcp_actions.approve`). Approving runs
 * the action as the approving staff member, who must also hold the tool's own permission.
 */
class McpActionController extends Controller
{
    public function __construct(private McpPendingActionService $service, private McpPendingActionRepository $actions) {}

    public function index(ListMcpActionsRequest $request): JsonResponse
    {
        $this->service->expireDue();

        return $this->paginated($this->actions->adminPage($request->status(), $request->page(), $request->perPage()), McpAdminActionResource::class);
    }

    public function approve(DecideMcpActionRequest $request, McpPendingAction $mcpAction): JsonResponse
    {
        $action = $this->service->approve($mcpAction, $request->user(), $request->validated('note'));

        return $this->success(McpAdminActionResource::make($action->load(['serviceAccount:id,name', 'approvedBy:id,name']))->resolve(), 'Approved.');
    }

    public function reject(DecideMcpActionRequest $request, McpPendingAction $mcpAction): JsonResponse
    {
        $action = $this->service->reject($mcpAction, $request->user(), (string) $request->validated('note'));

        return $this->success(McpAdminActionResource::make($action->load(['serviceAccount:id,name', 'approvedBy:id,name']))->resolve(), 'Rejected.');
    }
}
