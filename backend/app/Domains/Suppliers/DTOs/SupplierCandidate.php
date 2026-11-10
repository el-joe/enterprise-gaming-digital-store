<?php

namespace App\Domains\Suppliers\DTOs;

use App\Domains\Shared\ValueObjects\Money;

/**
 * One mapping that passed every selection filter, with what the gate needs to submit to it.
 */
final readonly class SupplierCandidate
{
    public function __construct(
        public int $mappingId,
        public int $supplierId,
        public string $supplierProductExternalId,
        public string $unitPrice,
        public string $unitCurrency,
        public Money $baseCost,
        public int $priority,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'mapping_id' => $this->mappingId,
            'supplier_id' => $this->supplierId,
            'supplier_product_id' => $this->supplierProductExternalId,
            'unit_price' => $this->unitPrice,
            'unit_currency' => $this->unitCurrency,
            'base_cost' => $this->baseCost->amount,
            'priority' => $this->priority,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromArray(array $row, string $baseCurrency): self
    {
        return new self(
            (int) $row['mapping_id'],
            (int) $row['supplier_id'],
            (string) $row['supplier_product_id'],
            (string) $row['unit_price'],
            (string) $row['unit_currency'],
            Money::of((string) $row['base_cost'], $baseCurrency),
            (int) $row['priority'],
        );
    }
}
