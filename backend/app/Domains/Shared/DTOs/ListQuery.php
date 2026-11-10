<?php

namespace App\Domains\Shared\DTOs;

/**
 * Validated pagination, filter, sort and search input for a list endpoint.
 */
final readonly class ListQuery
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public int $page = 1,
        public int $perPage = 20,
        public ?string $sort = null,
        public array $filters = [],
        public ?string $search = null,
    ) {}
}
