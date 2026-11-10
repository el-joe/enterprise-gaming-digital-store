<?php

namespace App\Domains\Catalog\Models;

use Database\Factories\Catalog\ContentTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('content_translations')]
#[Fillable(['translatable_type', 'translatable_id', 'locale', 'field', 'value'])]
#[UseFactory(ContentTranslationFactory::class)]
class ContentTranslation extends Model
{
    /** @use HasFactory<ContentTranslationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'translatable_id' => 'integer',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo('translatable', 'translatable_type', 'translatable_id');
    }
}
