<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use Database\Factories\Catalog\DeliveryFieldDefinitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('delivery_field_definitions')]
#[Fillable(['code', 'label', 'input_type', 'validation_regex', 'options', 'help_text'])]
#[UseFactory(DeliveryFieldDefinitionFactory::class)]
class DeliveryFieldDefinition extends Model
{
    /** @use HasFactory<DeliveryFieldDefinitionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_type' => DeliveryFieldDefinitionInputType::class,
            'options' => 'array',
        ];
    }

    /**
     * @return HasMany<ProductDeliveryField, $this>
     */
    public function productDeliveryFields(): HasMany
    {
        return $this->hasMany(ProductDeliveryField::class, 'delivery_field_definition_id');
    }
}
