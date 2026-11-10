<?php

namespace App\Domains\Shared\Models\Concerns;

use App\Domains\Shared\Exceptions\AppendOnlyViolation;

/**
 * Forbids model-level updates and deletes on append-only tables ([append] in DATABASE_DESIGN.md).
 * Retention pruning is done by console commands through the query builder.
 */
trait AppendOnly
{
    protected static function bootAppendOnly(): void
    {
        static::updating(static function (): never {
            throw new AppendOnlyViolation(static::class.' rows are append-only.');
        });

        static::deleting(static function (): never {
            throw new AppendOnlyViolation(static::class.' rows are append-only.');
        });
    }
}
