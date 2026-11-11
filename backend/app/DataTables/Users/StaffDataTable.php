<?php

namespace App\DataTables\Users;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Staff accounts (spec Phase 9.11, Figma "Admin Users and Permissions"): roles, two-step verification and status.
 */
class StaffDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'staff';
    }

    public static function permission(): string
    {
        return 'users.manage';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'users.name', html: true),
            new Column('email', __('admin.col.email'), 'users.email', html: true),
            new Column('roles', __('admin.col.roles'), html: true),
            new Column('two_factor', __('admin.col.two_factor'), 'users.two_factor_confirmed_at', html: true),
            new Column('status', __('admin.col.status'), 'users.status', html: true),
            new Column('last_login', __('admin.col.last_login'), 'users.last_login_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['name', 'asc'];
    }

    protected function searchColumns(): array
    {
        return ['users.name', 'users.email'];
    }

    public function filters(): array
    {
        return [
            new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('users.status', $value);
            }, $this->enumOptions('user_status', UserStatus::cases())),
            new Filter('two_factor', __('admin.col.two_factor'), 'select', static function (Builder $query, string $value): void {
                $value === '1' ? $query->whereNotNull('users.two_factor_confirmed_at') : $query->whereNull('users.two_factor_confirmed_at');
            }, ['1' => __('admin.users.on'), '0' => __('admin.users.off')]),
        ];
    }

    public function rowId(Model $row): string
    {
        return (string) $row->getAttribute('public_id');
    }

    protected function idColumn(): string
    {
        return 'users.public_id';
    }

    protected function query(): Builder
    {
        return User::query()->select(['users.id', 'users.public_id', 'users.name', 'users.email', 'users.status', 'users.two_factor_confirmed_at', 'users.last_login_at'])->where('users.type', UserType::Staff->value)->with('roles:id,name');
    }

    /**
     * @param  User  $row
     */
    protected function cells(Model $row): array
    {

        return [
            'name' => Cell::link(route('admin.users.staff.show', $row->public_id), (string) $row->name),
            'email' => Cell::mono($row->email),
            'roles' => implode(' ', array_map(static fn (string $role): string => Cell::badge($role, 'neutral', false), $row->roles->pluck('name')->all())) ?: Cell::muted('—'),
            'two_factor' => $row->two_factor_confirmed_at !== null ? Cell::badge(__('admin.users.on'), 'success', false) : Cell::badge(__('admin.users.off'), 'danger', false),
            'status' => Cell::status('user_status', $row->status?->value),
            'last_login' => $this->dateTime($row->last_login_at),
        ];
    }
}
