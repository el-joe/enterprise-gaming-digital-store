<?php

namespace App\DataTables\Customers;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Services\PiiMasker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Customers (spec Phase 9.5, Figma "Admin / Customers / List"): status, risk, orders and lifetime value. Email is masked and not searchable without `customers.view_pii`.
 */
class CustomersDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'customers';
    }

    public static function permission(): string
    {
        return 'customers.view';
    }

    public function columns(): array
    {
        return [
            new Column('customer', __('admin.col.customer'), 'users.name', html: true),
            new Column('email', __('admin.col.email'), html: true),
            new Column('status', __('admin.col.status'), 'users.status', html: true),
            new Column('risk', __('admin.col.risk'), 'customer_profiles.risk_level', html: true),
            new Column('orders', __('admin.col.orders'), 'customer_profiles.orders_count', numeric: true),
            new Column('lifetime', __('admin.col.lifetime'), 'customer_profiles.lifetime_value_base', html: true, numeric: true),
            new Column('country', __('admin.col.country'), 'users.country_code'),
            new Column('last_order', __('admin.col.last_order'), 'customer_profiles.last_order_at'),
            new Column('created', __('admin.col.created'), 'users.created_at'),
        ];
    }

    public function defaultOrder(): array
    {
        return ['created', 'desc'];
    }

    protected function searchColumns(): array
    {
        return $this->viewer->can('customers.view_pii')
            ? ['users.name', 'users.email', 'users.public_id', 'users.phone']
            : ['users.name', 'users.public_id'];
    }

    public function filters(): array
    {
        return [
            new Filter('status', __('admin.col.status'), 'select', static function (Builder $query, string $value): void {
                $query->where('users.status', $value);
            }, $this->enumOptions('user_status', UserStatus::cases())),
            new Filter('risk', __('admin.col.risk'), 'select', static function (Builder $query, string $value): void {
                $query->where('customer_profiles.risk_level', $value);
            }, $this->enumOptions('risk_level', CustomerRiskLevel::cases())),
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
        return User::query()
            ->select(['users.id', 'users.public_id', 'users.name', 'users.email', 'users.status', 'users.country_code', 'users.created_at',
                'customer_profiles.risk_level', 'customer_profiles.orders_count', 'customer_profiles.lifetime_value_base', 'customer_profiles.last_order_at'])
            ->leftJoin('customer_profiles', 'customer_profiles.user_id', '=', 'users.id')
            ->where('users.type', UserType::Customer->value);
    }

    /**
     * @param  User  $row
     */
    protected function cells(Model $row): array
    {
        $pii = $this->viewer->can('customers.view_pii');
        $base = app(ExchangeRateRegistry::class)->baseCurrency();
        $risk = $row->getAttribute('risk_level');
        $last = $row->getAttribute('last_order_at');

        return [
            'customer' => Cell::link(route('admin.customers.show', $row->public_id), (string) $row->name),
            'email' => Cell::mono($pii ? $row->email : app(PiiMasker::class)->email($row->email)),
            'status' => Cell::status('user_status', $row->status?->value),
            'risk' => Cell::status('risk_level', is_string($risk) ? $risk : 'low'),
            'orders' => (string) ($row->getAttribute('orders_count') ?? 0),
            'lifetime' => Cell::strong($this->money($row->getAttribute('lifetime_value_base') ?? '0', $base)),
            'country' => Cell::mono($row->country_code),
            'last_order' => $last === null ? '—' : $this->dateTime(Carbon::parse($last)),
            'created' => $this->dateTime($row->created_at),
        ];
    }
}
