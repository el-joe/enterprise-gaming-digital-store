<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Mcp\Services\McpPendingActionService;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminTablePage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * AI and automation (M15): the AI draft review queue, the MCP action approval queue, MCP tokens
 * and n8n credentials with workflow switches. These screens wrap the M16 and M17 JSON endpoints.
 * A new token or secret is shown once, in the issue response, and never stored in the page.
 */
class AutomationPageController extends Controller
{
    public function aiSuggestions(Request $request, AdminTablePage $tables): View
    {
        return view('admin.automation.table', ['tab' => 'ai-suggestions', 'table' => $tables->make('ai-suggestions', $request->user())]);
    }

    public function mcpActions(Request $request, AdminTablePage $tables, McpPendingActionService $actions): View
    {
        $actions->expireDue();

        return view('admin.automation.table', ['tab' => 'mcp-actions', 'table' => $tables->make('mcp-actions', $request->user())]);
    }

    public function mcpTokens(Request $request, AdminTablePage $tables): Response
    {
        return response()->view('admin.automation.mcp-tokens', [
            'table' => $tables->make('mcp-tokens', $request->user()),
            'scopes' => array_map(static fn (McpScope $scope): string => $scope->value, McpScope::cases()),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function credentials(Request $request, AdminTablePage $tables): Response
    {
        return response()->view('admin.automation.credentials', [
            'table' => $tables->make('automation-credentials', $request->user()),
            'workflows' => $tables->make('automation-workflows', $request->user()),
            'scopes' => array_map(static fn (AutomationScope $scope): string => $scope->value, AutomationScope::cases()),
        ])->header('Cache-Control', 'no-store, private');
    }
}
