<?php

namespace App\Domains\Catalog\DTOs;

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;

/**
 * One delivery field of a product exactly as `DeliveryFieldValidator` enforces it (M-A4). The
 * storefront publishes these same objects, so the published definition and the cart's
 * validation cannot drift.
 */
final readonly class DeliveryFieldSpec
{
    /**
     * @param  string|null  $pattern  admin expression that must match the whole (trimmed) value
     * @param  list<array{value: string, label: string}>  $options  allowed values of a select
     */
    public function __construct(
        public DeliveryFieldDefinition $definition,
        public string $code,
        public string $label,
        public DeliveryFieldDefinitionInputType $type,
        public bool $required,
        public ?string $pattern,
        public array $options,
        public int $maxLength,
    ) {}
}
