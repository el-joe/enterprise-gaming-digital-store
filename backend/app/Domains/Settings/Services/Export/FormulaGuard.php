<?php

namespace App\Domains\Settings\Services\Export;

/**
 * Spreadsheet formula injection guard (OWASP CSV injection): a cell that starts with `=`, `+`,
 * `-`, `@`, a tab or a carriage return is prefixed with an apostrophe so it stays text.
 */
final class FormulaGuard
{
    public static function clean(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric($value)) {
            return "'".$value;
        }

        return $value;
    }
}
