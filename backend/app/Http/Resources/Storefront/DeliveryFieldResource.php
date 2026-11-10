<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Catalog\DTOs\DeliveryFieldSpec;
use App\Domains\Catalog\Services\ContentTranslator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A delivery field definition as `POST /cart/items` validates `delivery_values` (M-A4). Built from
 * the same DeliveryFieldSpec the validator uses: values are trimmed, an empty value counts as
 * missing, `pattern` must match the whole value (PCRE, Unicode), `number` means digits only,
 * `select` accepts only an option `value`, and keys outside the list are refused.
 *
 * @property-read DeliveryFieldSpec $resource
 */
class DeliveryFieldResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $spec = $this->resource;
        $translator = app(ContentTranslator::class);

        return [
            'key' => $spec->code,
            'label' => $translator->text($spec->definition, 'label'),
            'help_text' => $translator->text($spec->definition, 'help_text'),
            'type' => $spec->type->value,
            'required' => $spec->required,
            'max_length' => $spec->maxLength,
            'pattern' => $spec->pattern,
            'options' => $spec->options === [] ? null : $spec->options,
        ];
    }
}
