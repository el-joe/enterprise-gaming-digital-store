<?php

namespace App\DataTables\Automation;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Mcp\Enums\McpPendingActionStatus;
use App\Domains\Mcp\Models\McpPendingAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The MCP action approval queue (M15, ADR-020): writes requested by the AI assistant wait for a person. Approving runs the action through the same services, as the approving staff member.
 */
class McpActionsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'mcp-actions';
    }

    public static function permission(): string
    {
        return 'mcp_actions.approve';
    }

    public function columns(): array
    {
        return [
            new Column('created', __('admin.col.created'), 'mcp_pending_actions.created_at'),
            new Column('tool', __('admin.col.tool'), 'mcp_pending_actions.tool', html: true),
            new Column('summary', __('admin.col.summary'), html: true),
            new Column('tier', __('admin.col.tier'), 'mcp_pending_actions.risk_tier', numeric: true),
            new Column('account', __('admin.col.account'), html: true),
            new Column('status', __('admin.col.status'), 'mcp_pending_actions.status', html: true),
            new Column('expires', __('admin.col.expires'), 'mcp_pending_actions.expires_at'),
            new Column('actions', __('admin.col.actions'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    public function filters(): array
    {
        return [new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
            $query->where('mcp_pending_actions.status', $value);
        }, $this->enumOptions('mcp_status', McpPendingActionStatus::cases()))];
    }

    public function rowId(Model $row): string
    {
        return (string) $row->getAttribute('public_id');
    }

    protected function idColumn(): string
    {
        return 'mcp_pending_actions.public_id';
    }

    protected function query(): Builder
    {
        return McpPendingAction::query()->select('mcp_pending_actions.*')->with(['serviceAccount:id,name', 'approvedBy:id,name']);
    }

    /**
     * @param  McpPendingAction  $row
     */
    protected function cells(Model $row): array
    {
        $api = url('/admin/api/mcp-actions/'.$row->public_id);
        $open = $row->status === McpPendingActionStatus::Pending && ! $row->hasExpired();

        return [
            'created' => $this->dateTime($row->created_at),
            'tool' => Cell::mono($row->tool),
            'summary' => Cell::stack((string) $row->summary, $row->approvedBy === null ? null : __('admin.mcp.decided_by', ['name' => $row->approvedBy->name])),
            'tier' => (string) $row->risk_tier,
            'account' => e((string) ($row->serviceAccount?->name ?? '—')),
            'status' => Cell::status('mcp_status', $row->status?->value),
            'expires' => $this->dateTime($row->expires_at),
            'actions' => ! $open ? '' : Cell::actions(
                Cell::action(__('admin.common.approve'), $api.'/approve', confirm: __('admin.mcp.approve_confirm')),
                Cell::action(__('admin.common.reject'), $api.'/reject', confirm: __('admin.mcp.reject_confirm'), danger: true, reason: __('admin.common.note'), reasonField: 'note'),
            ),
        ];
    }
}
