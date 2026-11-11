<?php

namespace App\DataTables\Suppliers;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Suppliers\Models\SupplierApiLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A supplier's API calls (spec Phase 9.3 "API logs"). Only the operation, method, URL path,
 * status, duration and outcome are shown; the redacted bodies stay in the database.
 */
class SupplierApiLogsDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'supplier-logs';
    }

    public static function permission(): string
    {
        return 'suppliers.logs';
    }

    public function columns(): array
    {
        return [
            new Column('time', __('admin.logs.col.time'), 'supplier_api_logs.created_at'),
            new Column('operation', __('admin.logs.col.operation'), 'supplier_api_logs.operation', html: true),
            new Column('endpoint', __('admin.logs.col.endpoint'), html: true),
            new Column('outcome', __('admin.logs.col.outcome'), 'supplier_api_logs.succeeded', html: true),
            new Column('http', __('admin.logs.col.http'), 'supplier_api_logs.http_status', numeric: true),
            new Column('duration', __('admin.logs.col.duration'), 'supplier_api_logs.duration_ms', numeric: true),
            new Column('reference', __('admin.logs.col.reference'), html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['time', 'desc'];
    }

    public function filters(): array
    {
        return [
            new Filter('supplier', '', 'context', static function (Builder $query, string $value): void {
                $query->where('supplier_api_logs.supplier_id', (int) $value);
            }),
            new Filter('outcome', __('admin.logs.col.outcome'), 'select', static function (Builder $query, string $value): void {
                $query->where('supplier_api_logs.succeeded', $value === 'ok');
            }, ['ok' => __('admin.logs.succeeded'), 'failed' => __('admin.logs.failed')]),
        ];
    }

    protected function searchColumns(): array
    {
        return ['supplier_api_logs.operation'];
    }

    protected function query(): Builder
    {
        return SupplierApiLog::query()->select('supplier_api_logs.*')->with('supplierTransaction:id,public_id,client_reference');
    }

    /**
     * @param  SupplierApiLog  $row
     */
    protected function cells(Model $row): array
    {
        $path = (string) (parse_url((string) $row->url, PHP_URL_PATH) ?? '');

        return [
            'time' => $this->dateTime($row->created_at),
            'operation' => Cell::mono($row->operation),
            'endpoint' => Cell::stack(trim(((string) $row->method).' '.$path), null),
            'outcome' => $row->succeeded ? Cell::badge(__('admin.logs.succeeded'), 'success') : Cell::badge(__('admin.logs.failed'), 'danger'),
            'http' => $row->http_status === null ? '—' : (string) $row->http_status,
            'duration' => $row->duration_ms === null ? '—' : $row->duration_ms.' ms',
            'reference' => Cell::mono($row->supplierTransaction?->client_reference),
        ];
    }
}
