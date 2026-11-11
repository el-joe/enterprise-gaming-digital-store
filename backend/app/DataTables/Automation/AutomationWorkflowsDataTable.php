<?php

namespace App\DataTables\Automation;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Automation\Models\AutomationWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The 58 n8n workflows (M17): enabled or not, schedule and last outcome. Only enabled workflows may call the automation API.
 */
class AutomationWorkflowsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'automation-workflows';
    }

    public static function permission(): string
    {
        return 'service_accounts.manage';
    }

    public function columns(): array
    {
        return [
            new Column('workflow', __('admin.col.workflow'), 'automation_workflows.workflow_id', html: true),
            new Column('name', __('admin.col.name'), 'automation_workflows.name'),
            new Column('category', __('admin.col.category'), 'automation_workflows.category'),
            new Column('schedule', __('admin.col.schedule'), 'automation_workflows.schedule'),
            new Column('last_success', __('admin.col.last_success'), 'automation_workflows.last_success_at'),
            new Column('last_failure', __('admin.col.last_failure'), 'automation_workflows.last_failure_at'),
            new Column('enabled', __('admin.col.enabled'), 'automation_workflows.is_enabled', html: true),
            new Column('actions', __('admin.col.actions'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['workflow', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['automation_workflows.workflow_id', 'automation_workflows.name'];
    }

    public function filters(): array
    {
        return [$this->booleanFilter('enabled', __('admin.col.enabled'), 'automation_workflows.is_enabled')];
    }

    protected function query(): Builder
    {
        return AutomationWorkflow::query()->select('automation_workflows.*');
    }

    /**
     * @param  AutomationWorkflow  $row
     */
    protected function cells(Model $row): array
    {
        $api = url('/admin/api/automation-workflows/'.$row->workflow_id);

        return [
            'workflow' => Cell::mono($row->workflow_id),
            'name' => (string) $row->name,
            'category' => (string) $row->category,
            'schedule' => (string) ($row->schedule ?? '—'),
            'last_success' => $this->dateTime($row->last_success_at),
            'last_failure' => $this->dateTime($row->last_failure_at),
            'enabled' => Cell::yesNo((bool) $row->is_enabled),
            'actions' => $row->is_enabled
                ? Cell::action(__('admin.automation.disable'), $api.'/disable', confirm: __('admin.automation.disable_confirm'), danger: true)
                : Cell::action(__('admin.automation.enable'), $api.'/enable', confirm: __('admin.automation.enable_confirm')),
        ];
    }
}
