<?php

namespace App\DataTables;

/**
 * One column of an admin table (M14). `orderBy` is the allow-listed SQL expression the column
 * sorts by (null: not sortable); `html` columns are rendered by the table class from escaped
 * parts and sent raw, every other column is escaped by Yajra.
 */
final readonly class Column
{
    public function __construct(
        public string $key,
        public string $label,
        public ?string $orderBy = null,
        public bool $html = false,
        public bool $visible = true,
        public bool $exportable = true,
        public bool $numeric = false,
    ) {}
}
