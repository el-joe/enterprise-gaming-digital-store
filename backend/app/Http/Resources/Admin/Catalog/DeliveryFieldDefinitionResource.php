<?php

namespace App\Http\Resources\Admin\Catalog;

use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeliveryFieldDefinition
 */
class DeliveryFieldDefinitionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'input_type' => $this->input_type->value,
            'validation_regex' => $this->validation_regex,
            'options' => $this->options,
            'help_text' => $this->help_text,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
