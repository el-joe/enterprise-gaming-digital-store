<?php

namespace App\Domains\Currency\DTOs;

final readonly class RateRefreshResult
{
    /**
     * @param  list<string>  $updated
     * @param  list<string>  $held
     * @param  list<string>  $duplicates
     * @param  list<string>  $missing
     */
    public function __construct(
        public array $updated,
        public array $held,
        public array $duplicates,
        public array $missing,
    ) {}

    /**
     * @return array{updated: int, held: list<string>, duplicates: int, missing: int}
     */
    public function summary(): array
    {
        return ['updated' => count($this->updated), 'held' => $this->held, 'duplicates' => count($this->duplicates), 'missing' => count($this->missing)];
    }
}
