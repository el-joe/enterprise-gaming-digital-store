<?php

namespace App\Domains\Catalog\Rules;

use App\Domains\Catalog\Services\ReservedSlugs;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotReservedSlug implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && app(ReservedSlugs::class)->isReserved($value)) {
            $fail("The {$attribute} \"{$value}\" is reserved by a site route.");
        }
    }
}
