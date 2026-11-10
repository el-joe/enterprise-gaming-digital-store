<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDeliveryField;
use Illuminate\Validation\ValidationException;

/**
 * Validates customer-supplied delivery values (Player ID, Server ID and so on) against the
 * fields configured on a product. A pattern must match the whole value, and a pattern that
 * fails at runtime rejects the value rather than letting it through.
 */
class DeliveryFieldValidator
{
    private const int MAX_VALUE_LENGTH = 255;

    public static function compiles(string $regex): bool
    {
        return @preg_match(self::pattern($regex), '') !== false;
    }

    /**
     * @param  array<string, mixed>  $values  keyed by delivery field code
     * @return array<string, string> the accepted values, normalized and keyed by code
     *
     * @throws ValidationException
     */
    public function validate(Product $product, array $values): array
    {
        $fields = $product->productDeliveryFields()->with('deliveryFieldDefinition')->orderBy('sort_order')->get();

        $errors = [];
        $accepted = [];

        foreach ($fields as $field) {
            /** @var ProductDeliveryField $field */
            $definition = $field->deliveryFieldDefinition;
            $value = $values[$definition->code] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            if ($value === null || $value === '') {
                if ($field->is_required) {
                    $errors["delivery_values.{$definition->code}"] = ["{$definition->label} is required."];
                }

                continue;
            }

            $error = $this->errorFor($definition, $value);

            if ($error !== null) {
                $errors["delivery_values.{$definition->code}"] = [$error];

                continue;
            }

            $accepted[$definition->code] = $value;
        }

        $known = $fields->map(static fn (ProductDeliveryField $field): string => $field->deliveryFieldDefinition->code)->all();

        foreach (array_diff(array_keys($values), $known) as $unknown) {
            $errors["delivery_values.{$unknown}"] = ['This field is not accepted for this product.'];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $accepted;
    }

    private function errorFor(DeliveryFieldDefinition $definition, mixed $value): ?string
    {
        if (! is_string($value) || mb_strlen($value) > self::MAX_VALUE_LENGTH) {
            return "{$definition->label} is not valid.";
        }

        $typeIsValid = match ($definition->input_type) {
            DeliveryFieldDefinitionInputType::Number => preg_match('/^\d+$/', $value) === 1,
            DeliveryFieldDefinitionInputType::Email => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            DeliveryFieldDefinitionInputType::Select => in_array($value, array_column($definition->options ?? [], 'value'), true),
            DeliveryFieldDefinitionInputType::Text => true,
        };

        if (! $typeIsValid) {
            return "{$definition->label} is not valid.";
        }

        if ($definition->validation_regex !== null && @preg_match(self::pattern($definition->validation_regex), $value) !== 1) {
            return "{$definition->label} has an invalid format.";
        }

        return null;
    }

    /**
     * Wrap an admin-entered expression so it must match the entire value. The control character
     * delimiter cannot occur in a stored expression, so no escaping of the expression is needed.
     */
    private static function pattern(string $regex): string
    {
        return "\x01^(?:{$regex})$\x01uD";
    }
}
