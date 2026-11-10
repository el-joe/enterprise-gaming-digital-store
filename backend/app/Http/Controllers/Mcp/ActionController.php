<?php

namespace App\Http\Controllers\Mcp;

use App\Domains\Mcp\Services\McpPendingActionService;
use App\Http\Requests\Mcp\ConfirmActionRequest;
use App\Http\Requests\Mcp\WriteActionRequest;
use App\Http\Resources\Mcp\McpPendingActionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Write tools create pending actions (202); nothing changes until approval.
 */
class ActionController extends McpController
{
    public function __construct(private McpPendingActionService $actions) {}

    public function request(WriteActionRequest $request): JsonResponse
    {
        ['action' => $action, 'confirmation_token' => $token] = $this->actions->request($this->caller($request), $request->tool(), $request->validated());

        return $this->success([
            ...McpPendingActionResource::make($action)->resolve(),
            'approval' => [
                'admin_panel' => true,
                'elicitation' => $token !== null,
            ],
            ...($token === null ? [] : ['confirmation_token' => $token]),
        ], 'Pending approval. Nothing has changed yet.', status: 202);
    }

    public function confirm(ConfirmActionRequest $request, string $actionId): JsonResponse
    {
        $action = $this->actions->confirm($this->caller($request), $actionId, (string) $request->validated('confirmation_token'));

        return $this->success(McpPendingActionResource::make($action)->resolve(), 'Confirmed.');
    }

    public function show(Request $request, string $actionId): JsonResponse
    {
        return $this->success(McpPendingActionResource::make($this->actions->status($this->caller($request), $actionId))->resolve());
    }
}
