<?php

namespace App\Domains\Suppliers\DTOs;

use LogicException;

final readonly class DeliverablePayload
{
    /**
     * @param  list<DeliverableItem>  $items
     */
    public function __construct(public array $items) {}

    /**
     * @return array<string, int>
     */
    public function __debugInfo(): array
    {
        return ['items' => count($this->items)];
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        throw new LogicException('Digital code material cannot be serialized.');
    }
}
