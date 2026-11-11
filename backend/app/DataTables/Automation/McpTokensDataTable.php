<?php

namespace App\DataTables\Automation;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\Enums\McpScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * MCP tokens (M15, M16): account, scopes, read-only, last use and expiry. A token itself is shown once at issue and never again.
 */
class McpTokensDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'mcp-tokens';
    }

    public static function permission(): string
    {
        return 'service_accounts.manage';
    }

    public function columns(): array
    {
        return [
            new Column('account', __('admin.col.account'), 'service_accounts.name', html: true),
            new Column('name', __('admin.col.name'), 'personal_access_tokens.name'),
            new Column('scopes', __('admin.col.scopes'), html: true),
            new Column('access', __('admin.col.access'), html: true),
            new Column('last_used', __('admin.col.last_used'), 'personal_access_tokens.last_used_at'),
            new Column('expires', __('admin.col.expires'), 'personal_access_tokens.expires_at'),
            new Column('actions', __('admin.col.actions'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['expires', 'desc'];
    }

    protected function idColumn(): string
    {
        return 'personal_access_tokens.id';
    }

    protected function searchColumns(): array
    {
        return ['service_accounts.name'];
    }

    protected function query(): Builder
    {
        return PersonalAccessToken::query()
            ->select(['personal_access_tokens.id', 'personal_access_tokens.name', 'personal_access_tokens.abilities', 'personal_access_tokens.last_used_at', 'personal_access_tokens.expires_at', 'service_accounts.name as account_name', 'service_accounts.is_read_only'])
            ->join('service_accounts', 'service_accounts.id', '=', 'personal_access_tokens.tokenable_id')
            ->where('personal_access_tokens.tokenable_type', (new ServiceAccount)->getMorphClass())
            ->where('service_accounts.kind', ServiceAccountKind::Mcp->value);
    }

    /**
     * @param  PersonalAccessToken  $row
     */
    protected function cells(Model $row): array
    {

        return [
            'account' => e((string) $row->getAttribute('account_name')),
            'name' => (string) $row->name,
            'scopes' => Cell::mono(implode(', ', array_map(static fn (McpScope $scope): string => $scope->value, McpScope::fromAbilities($row->abilities ?? [])))),
            'access' => $row->getAttribute('is_read_only') ? Cell::badge(__('admin.mcp.read_only'), 'success', false) : Cell::badge(__('admin.mcp.writes'), 'warning', false),
            'last_used' => $this->dateTime($row->last_used_at),
            'expires' => $this->dateTime($row->expires_at),
            'actions' => Cell::action(__('admin.common.revoke'), url('/admin/api/mcp-tokens/'.$row->id), 'DELETE', confirm: __('admin.mcp.revoke_confirm'), danger: true),
        ];
    }
}
