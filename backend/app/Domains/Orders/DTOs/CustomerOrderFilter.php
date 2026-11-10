<?php

namespace App\Domains\Orders\DTOs;

use App\Domains\Orders\Enums\OrderStatus;
use Carbon\CarbonImmutable;

/**
 * Filters of the customer's order history (M-A5): one status, and an inclusive range of placement
 * dates (`from` at the start of its day, `to` at the end of its day, application time zone).
 */
final readonly class CustomerOrderFilter
{
    public function __construct(
        public ?OrderStatus $status = null,
        public ?CarbonImmutable $from = null,
        public ?CarbonImmutable $to = null,
    ) {}
}
