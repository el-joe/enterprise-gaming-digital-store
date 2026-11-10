<?php

namespace App\Domains\Suppliers\Adapters;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Fulfillment\Services\StockAllocator;
use App\Domains\Suppliers\DTOs\AvailabilityQuery;
use App\Domains\Suppliers\DTOs\AvailabilityResult;
use App\Domains\Suppliers\DTOs\ConnectionResult;
use App\Domains\Suppliers\DTOs\SupplierCapabilities;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\DTOs\SupplierOrderRequest;
use App\Domains\Suppliers\Enums\CapabilityLevel;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Enums\SupplierCapability as Cap;
use App\Domains\Suppliers\Models\Supplier;
use Carbon\CarbonInterface;

/**
 * Codes loaded by staff into digital_codes (architecture 7.1). A purchase reserves one available
 * code with SELECT ... FOR UPDATE SKIP LOCKED; the code is already in the vault, so the outcome
 * carries its id instead of a deliverable fetch. No stock is a definitive refusal.
 */
class InternalStockAdapter extends AbstractSupplierAdapter
{
    public const string KEY = 'internal_stock';

    public function __construct(Supplier $supplier, private StockAllocator $stock)
    {
        parent::__construct($supplier);
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function capabilities(): SupplierCapabilities
    {
        return new SupplierCapabilities(
            levels: [
                Cap::CreateOrder->value => CapabilityLevel::Full,
                Cap::OrderStatus->value => CapabilityLevel::Full,
                Cap::LookupByReference->value => CapabilityLevel::Full,
                Cap::Availability->value => CapabilityLevel::Full,
                Cap::ClientIdempotency->value => CapabilityLevel::Full,
            ],
            supportedDeliveryTypes: [DeliveryType::InstantCode, DeliveryType::ApiDelivery, DeliveryType::SubscriptionActivation],
        );
    }

    public function testConnection(): ConnectionResult
    {
        return new ConnectionResult(true, 0);
    }

    public function checkAvailability(AvailabilityQuery $query): AvailabilityResult
    {
        $available = $query->productVariantId !== null && $this->stock->availableCount($query->productVariantId) > 0;

        return new AvailabilityResult($available ? SupplierAvailability::Available : SupplierAvailability::Unavailable, advisory: false);
    }

    public function createOrder(SupplierOrderRequest $request): SupplierOrderOutcome
    {
        if ($request->productVariantId === null) {
            return SupplierOrderOutcome::rejected('invalid_mapping', notSent: true);
        }

        $code = $this->stock->reserve($request->productVariantId, $request->supplierTransactionId);

        return $code === null
            ? SupplierOrderOutcome::rejected('out_of_stock', notSent: true)
            : SupplierOrderOutcome::fulfilled('stock_reserved', $code->public_id, vaultCodeId: $code->id);
    }

    public function getOrderStatus(string $supplierTransactionId): SupplierOrderOutcome
    {
        $code = $this->stock->findByPublicId($supplierTransactionId);

        return $code === null ? SupplierOrderOutcome::notFound() : SupplierOrderOutcome::fulfilled('stock_reserved', $code->public_id, vaultCodeId: $code->id);
    }

    public function findOrderByReference(string $clientReference, CarbonInterface $since): SupplierOrderOutcome
    {
        $code = $this->stock->findReservedForReference($this->supplier->id, $clientReference);

        return $code === null ? SupplierOrderOutcome::notFound() : SupplierOrderOutcome::fulfilled('stock_reserved', $code->public_id, vaultCodeId: $code->id);
    }
}
