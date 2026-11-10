<?php

namespace App\Domains\Suppliers\Adapters;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Suppliers\DTOs\ConnectionResult;
use App\Domains\Suppliers\DTOs\SupplierCapabilities;

/**
 * Staff fulfilment (architecture 7.1, BD2). It places no supplier order: units of the delivery
 * types below become manual_fulfillment_tasks through ManualTaskStrategy, and staff complete them
 * through the admin API. Every supplier operation is declared `none`, so selection never picks it
 * and the capability matrix shows exactly what it does.
 */
class ManualFulfillmentAdapter extends AbstractSupplierAdapter
{
    public const string KEY = 'manual_fulfillment';

    public function key(): string
    {
        return self::KEY;
    }

    public function capabilities(): SupplierCapabilities
    {
        return new SupplierCapabilities(
            levels: [],
            supportedDeliveryTypes: [DeliveryType::DirectTopup, DeliveryType::AccountRecharge, DeliveryType::Manual, DeliveryType::SubscriptionActivation],
        );
    }

    public function testConnection(): ConnectionResult
    {
        return new ConnectionResult(true, 0);
    }
}
