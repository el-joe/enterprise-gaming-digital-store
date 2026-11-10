<?php

namespace App\Domains\Suppliers\DTOs;

/**
 * One unit for one supplier. `clientReference` is stable per unit and supplier and is sent as
 * the supplier's own reference, so a lookup can find the order after an ambiguous outcome.
 */
final readonly class SupplierOrderRequest
{
    /**
     * @param  array<string, string>  $deliveryValues  validated customer inputs, by field code
     */
    public function __construct(
        public string $clientReference,
        public string $supplierProductId,
        public string $unitPrice,
        public string $unitCurrency,
        public int $supplierTransactionId,
        public ?int $productVariantId = null,
        public array $deliveryValues = [],
    ) {}
}
