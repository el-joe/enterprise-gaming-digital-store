<?php

namespace App\Domains\Catalog\Models;

use Database\Factories\Catalog\SearchDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('search_documents')]
#[Fillable(['searchable_type', 'searchable_id', 'locale', 'title', 'keywords', 'boost', 'is_active'])]
#[UseFactory(SearchDocumentFactory::class)]
class SearchDocument extends Model
{
    /** @use HasFactory<SearchDocumentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'searchable_id' => 'integer',
            'boost' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function searchable(): MorphTo
    {
        return $this->morphTo('searchable', 'searchable_type', 'searchable_id');
    }
}
