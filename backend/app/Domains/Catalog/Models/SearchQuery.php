<?php

namespace App\Domains\Catalog\Models;

use Database\Factories\Catalog\SearchQueryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('search_queries')]
#[Fillable(['term', 'locale', 'results_count', 'last_searched_at'])]
#[UseFactory(SearchQueryFactory::class)]
class SearchQuery extends Model
{
    /** @use HasFactory<SearchQueryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hits' => 'integer',
            'results_count' => 'integer',
            'last_searched_at' => 'datetime',
        ];
    }
}
