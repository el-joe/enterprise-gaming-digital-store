<?php

namespace App\DataTables;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * A filter of an admin table (M14). `select` filters accept only their option keys; `date`
 * filters accept a Y-m-d date; `context` filters (the record a nested table belongs to) are
 * required and not shown as controls. The closure receives the query and the validated value.
 */
final readonly class Filter
{
    /**
     * @param  array<string, string>  $options  value => label (select)
     * @param  Closure(Builder<*>, string): void  $apply
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $type,
        public Closure $apply,
        public array $options = [],
    ) {}
}
