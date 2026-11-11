<?php

namespace App\DataTables\Customers;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Wallet\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The wallet ledger of one customer (spec Phase 9.5 "Wallet", "Transactions"): append-only movements with the balance after each.
 */
class CustomerWalletDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'customer-wallet';
    }

    public static function permission(): string
    {
        return 'customers.view';
    }

    public function columns(): array
    {
        return [
            new Column('date', __('admin.col.date'), 'wallet_transactions.created_at'),
            new Column('type', __('admin.col.type'), 'wallet_transactions.type', html: true),
            new Column('note', __('admin.col.note'), 'wallet_transactions.note'),
            new Column('by', __('admin.col.by'), html: true),
            new Column('amount', __('admin.col.amount'), 'wallet_transactions.amount', html: true, numeric: true),
            new Column('balance', __('admin.col.balance'), 'wallet_transactions.balance_after', numeric: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['date', 'desc'];
    }

    public function filters(): array
    {
        return [
            new Filter('customer', '', 'context', static function (Builder $query, string $value): void {
                $query->whereHas('customerWallet', static function (Builder $wallet) use ($value): void {
                    $wallet->where('user_id', (int) $value);
                });
            }),
        ];
    }

    protected function query(): Builder
    {
        return WalletTransaction::query()->select('wallet_transactions.*')->with(['customerWallet:id,currency', 'createdBy:id,name']);
    }

    /**
     * @param  WalletTransaction  $row
     */
    protected function cells(Model $row): array
    {
        $currency = (string) $row->customerWallet?->currency;
        $credit = bccomp((string) $row->amount, '0', 6) > 0;

        return [
            'date' => $this->dateTime($row->created_at),
            'type' => Cell::status('wallet_type', $row->type?->value),
            'note' => (string) ($row->note ?? '—'),
            'by' => e((string) ($row->createdBy?->name ?? __('admin.enums.actor.system'))),
            'amount' => Cell::strong(($credit ? '+' : '').$this->money($row->amount, $currency)),
            'balance' => $this->money($row->balance_after, $currency),
        ];
    }
}
