<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Suppliers\Models\SupplierTransaction;

/**
 * Locks an attempt's order, unit and transaction in the documented lock order (architecture
 * 9.4: orders, order_item_units, supplier_transactions). Call inside a transaction.
 */
class AttemptLocks
{
    /**
     * @return array{0: Order, 1: OrderItemUnit, 2: SupplierTransaction}|null
     */
    public function lock(int $supplierTransactionId): ?array
    {
        $unitId = SupplierTransaction::query()->whereKey($supplierTransactionId)->value('order_item_unit_id');

        if ($unitId === null) {
            return null;
        }

        $orderId = OrderItemUnit::query()->whereKey($unitId)->value('order_id');
        $order = Order::query()->lockForUpdate()->findOrFail($orderId);
        $unit = OrderItemUnit::query()->with('orderItem')->lockForUpdate()->findOrFail($unitId);
        $transaction = SupplierTransaction::query()->with('supplier')->lockForUpdate()->findOrFail($supplierTransactionId);

        return [$order, $unit, $transaction];
    }
}
