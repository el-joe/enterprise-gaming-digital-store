<?php

namespace App\Domains\Fulfillment\Strategies;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Fulfillment\Contracts\DeliveryStrategy;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\UnitStatusRecorder;
use App\Domains\Suppliers\Services\SupplierSelector;

/**
 * Codes from a supplier or internal stock: rank candidates, store the snapshot, queue the unit.
 * No candidate holds the unit for review (`no_supplier_available`), except subscription
 * activations, which may be fulfilled by staff.
 */
class CodeDeliveryStrategy implements DeliveryStrategy
{
    public function __construct(
        private SupplierSelector $selector,
        private UnitStatusRecorder $units,
        private ManualTaskStrategy $manual,
    ) {}

    public function plan(Order $order, OrderItemUnit $unit): ?int
    {
        $selection = $this->selector->rank($unit, $order);
        $unit->forceFill(['selection_snapshot' => $selection->toSnapshot()]);

        if ($selection->first() === null) {
            if ($unit->orderItem->delivery_type === DeliveryType::SubscriptionActivation) {
                return $this->manual->plan($order, $unit);
            }

            $this->units->move($unit, OrderItemUnitStatus::ManualReview, 'no_supplier_available', 'No supplier can fulfil this unit.', ['reasons' => array_column($selection->excluded, 'reason')]);

            return null;
        }

        $this->units->move($unit, OrderItemUnitStatus::Queued, 'queued', null, ['supplier_id' => $selection->first()->supplierId, 'mapping_id' => $selection->first()->mappingId]);

        return $unit->id;
    }
}
