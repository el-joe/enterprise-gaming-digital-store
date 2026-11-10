<?php

namespace App\Domains\Suppliers\DTOs;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\CapabilityLevel;
use App\Domains\Suppliers\Enums\SupplierCapability;

/**
 * An adapter's declared capability matrix (architecture 7.1). Capabilities not listed are `none`.
 */
final readonly class SupplierCapabilities
{
    /**
     * @param  array<string, CapabilityLevel>  $levels  keyed by SupplierCapability value
     * @param  list<DeliveryType>  $supportedDeliveryTypes
     * @param  list<string>  $sensitiveResponsePaths  response keys redacted before logging
     * @param  Money|null  $defaultPerRequestCap  the supplier's documented cap when the row sets none
     */
    public function __construct(
        public array $levels,
        public array $supportedDeliveryTypes,
        public array $sensitiveResponsePaths = [],
        public ?Money $defaultPerRequestCap = null,
    ) {}

    public function level(SupplierCapability $capability): CapabilityLevel
    {
        return $this->levels[$capability->value] ?? CapabilityLevel::None;
    }

    /**
     * Only a `full` declaration may be relied on by the engine.
     */
    public function supports(SupplierCapability $capability): bool
    {
        return $this->level($capability)->isUsable();
    }

    public function supportsDeliveryType(DeliveryType $type): bool
    {
        return in_array($type, $this->supportedDeliveryTypes, true);
    }

    /**
     * The matrix for the admin capability screen.
     *
     * @return array{capabilities: array<string, string>, delivery_types: list<string>, default_per_request_cap: array{amount: string, currency: string}|null}
     */
    public function toArray(): array
    {
        $matrix = [];

        foreach (SupplierCapability::cases() as $capability) {
            $matrix[$capability->value] = $this->level($capability)->value;
        }

        return [
            'capabilities' => $matrix,
            'delivery_types' => array_map(static fn (DeliveryType $type): string => $type->value, $this->supportedDeliveryTypes),
            'default_per_request_cap' => $this->defaultPerRequestCap?->jsonSerialize(),
        ];
    }
}
