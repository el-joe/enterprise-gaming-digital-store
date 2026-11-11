<?php

namespace App\DataTables;

use App\DataTables\Orders\OrdersDataTable;
use App\DataTables\Payments\DisputesDataTable;
use App\DataTables\Payments\PaymentsDataTable;
use App\DataTables\Payments\ReconciliationItemsDataTable;
use App\DataTables\Payments\RefundsDataTable;
use App\DataTables\Payments\WebhooksDataTable;
use App\DataTables\Suppliers\SupplierApiLogsDataTable;
use App\DataTables\Suppliers\SupplierMappingsDataTable;
use App\DataTables\Suppliers\SuppliersDataTable;
use App\Domains\Identity\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Every admin table by key (M14). routes/admin.php registers the data, export, state and saved
 * filter routes of each table with the table's own permission.
 */
final class AdminTables
{
    /** @var list<class-string<AdminDataTable>> */
    private const array TABLES = [
        OrdersDataTable::class,
        SuppliersDataTable::class,
        SupplierApiLogsDataTable::class,
        SupplierMappingsDataTable::class,
        PaymentsDataTable::class,
        WebhooksDataTable::class,
        RefundsDataTable::class,
        DisputesDataTable::class,
        ReconciliationItemsDataTable::class,
    ];

    /**
     * @return array<string, class-string<AdminDataTable>>
     */
    public static function all(): array
    {
        $tables = [];

        foreach (self::TABLES as $class) {
            $tables[$class::key()] = $class;
        }

        return $tables;
    }

    public static function make(string $key, User $viewer): AdminDataTable
    {
        $class = self::all()[$key] ?? throw new NotFoundHttpException;

        return new $class($viewer);
    }

    /**
     * The table key of the current admin table route (`admin.api.tables.{key}.*`).
     */
    public static function keyOfRoute(?string $routeName): string
    {
        $key = explode('.', (string) $routeName)[3] ?? '';

        return array_key_exists($key, self::all()) ? $key : throw new NotFoundHttpException;
    }
}
