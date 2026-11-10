<?php

namespace App\Domains\Catalog\Rules;

use App\Domains\Catalog\Services\DeliveryFieldValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompilableRegex implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! DeliveryFieldValidator::compiles($value)) {
            $fail("The {$attribute} field must be a valid regular expression.");
        }
    }
}
