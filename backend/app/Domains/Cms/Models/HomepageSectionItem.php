<?php

namespace App\Domains\Cms\Models;

use Database\Factories\Cms\HomepageSectionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('homepage_section_items')]
#[Fillable(['homepage_section_id', 'item_type', 'item_id', 'sort_order'])]
#[UseFactory(HomepageSectionItemFactory::class)]
class HomepageSectionItem extends Model
{
    /** @use HasFactory<HomepageSectionItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<HomepageSection, $this>
     */
    public function homepageSection(): BelongsTo
    {
        return $this->belongsTo(HomepageSection::class, 'homepage_section_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function item(): MorphTo
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }
}
