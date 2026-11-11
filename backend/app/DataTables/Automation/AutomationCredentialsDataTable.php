<?php

namespace App\DataTables\Automation;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * n8n automation credentials (M15, M17): account, scopes, signing secret state, last use and expiry. Token and secret are shown once at issue and never again.
 */
class AutomationCredentialsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'automation-credentials';
    }

    public static function permission(): string
    {
        return 'service_accounts.manage';
    }

    public function columns(): array
    {
        return [
            new Column('account', __('admin.col.account'), 'service_accounts.name', html: true),
            new Column('scopes', __('admin.col.scopes'), html: true),
            new Column('secret', __('admin.col.secret'), html: true),
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
            ->select(['personal_access_tokens.id', 'personal_access_tokens.abilities', 'personal_access_tokens.last_used_at', 'personal_access_tokens.expires_at', 'service_accounts.name as account_name'])
            ->selectRaw('service_accounts.hmac_secret is not null as has_secret')
            ->join('service_accounts', 'service_accounts.id', '=', 'personal_access_tokens.tokenable_id')
            ->where('personal_access_tokens.tokenable_type', (new ServiceAccount)->getMorphClass())
            ->where('service_accounts.kind', ServiceAccountKind::N8n->value);
    }

    /**
     * @param  PersonalAccessToken  $row
     */
    protected function cells(Model $row): array
    {

        return [
            'account' => e((string) $row->getAttribute('account_name')),
            'scopes' => Cell::mono(implode(', ', array_map(static fn (AutomationScope $scope): string => $scope->value, AutomationScope::fromAbilities($row->abilities ?? [])))),
            'secret' => (bool) $row->getAttribute('has_secret') ? Cell::badge(__('admin.common.configured'), 'success', false) : Cell::badge(__('admin.common.not_configured'), 'neutral', false),
            'last_used' => $this->dateTime($row->last_used_at),
            'expires' => $this->dateTime($row->expires_at),
            'actions' => Cell::action(__('admin.common.revoke'), url('/admin/api/automation-credentials/'.$row->id), 'DELETE', confirm: __('admin.automation.revoke_confirm'), danger: true),
        ];
    }
}
