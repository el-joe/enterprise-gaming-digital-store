<?php

namespace App\Domains\Shared\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts site-relative paths and absolute http(s) URLs, rejecting script and protocol-relative URLs.
 */
class SafeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('~^(/(?![/\\\\])|https?://)[^\s]*$~i', $value) !== 1) {
            $fail("The {$attribute} field must be a site-relative path or an http(s) URL.");
        }
    }
}
