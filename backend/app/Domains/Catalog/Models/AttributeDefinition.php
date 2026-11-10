<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\AttributeDefinitionType;
use Database\Factories\Catalog\AttributeDefinitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('attribute_definitions')]
#[Fillable(['code', 'name', 'type', 'options', 'is_filterable'])]
#[UseFactory(AttributeDefinitionFactory::class)]
class AttributeDefinition extends Model
{
    /** @use HasFactory<AttributeDefinitionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AttributeDefinitionType::class,
            'options' => 'array',
            'is_filterable' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ProductAttributeValue, $this>
     */
    public function productAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_definition_id');
    }
}
