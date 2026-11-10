<?php

namespace App\Domains\Suppliers\DTOs;

/**
 * Ranked candidates and, for audit, every mapping that was filtered out and why.
 */
final readonly class SelectionResult
{
    public const string BELOW_MARGIN = 'below_margin';

    /**
     * @param  list<SupplierCandidate>  $candidates
     * @param  list<array{mapping_id: int, supplier_id: int, reason: string}>  $excluded
     */
    public function __construct(
        public array $candidates,
        public array $excluded,
        public string $strategy,
    ) {}

    public function first(): ?SupplierCandidate
    {
        return $this->candidates[0] ?? null;
    }

    /**
     * True when at least one mapping would otherwise have qualified but fails the margin guard.
     */
    public function excludedByMargin(): bool
    {
        foreach ($this->excluded as $row) {
            if ($row['reason'] === self::BELOW_MARGIN) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSnapshot(): array
    {
        return [
            'strategy' => $this->strategy,
            'ranked_at' => now()->toIso8601String(),
            'candidates' => array_map(static fn (SupplierCandidate $candidate): array => $candidate->toArray(), $this->candidates),
            'excluded' => $this->excluded,
        ];
    }
}
