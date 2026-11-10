<?php

namespace App\Domains\Shared\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;

/**
 * Checks that an id refers to a real row of the morph type named by a sibling field.
 */
class MorphTargetExists implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(private string $typeField) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sibling = preg_replace('/[^.]+$/', $this->typeField, $attribute);
        $model = Relation::getMorphedModel((string) Arr::get($this->data, (string) $sibling));

        if ($model !== null && ! $model::query()->whereKey($value)->exists()) {
            $fail("The selected {$attribute} does not exist.");
        }
    }
}
