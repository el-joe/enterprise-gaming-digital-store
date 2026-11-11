<?php

namespace App\Domains\Seo\DTOs;

/**
 * Where an old path goes: a 301 or 302 to `target`, or 410 Gone (no target). `redirectIds` are
 * the rows followed, whose hits are counted.
 */
final readonly class RedirectMatch
{
    /**
     * @param  list<int>  $redirectIds
     */
    public function __construct(
        public int $status,
        public ?string $target,
        public array $redirectIds,
    ) {}

    public function isGone(): bool
    {
        return $this->status === 410;
    }
}
