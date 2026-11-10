<?php

namespace App\Domains\Cms\Models;

use App\Domains\Cms\Enums\HomepageSectionSource;
use Database\Factories\Cms\HomepageSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('homepage_sections')]
#[Fillable(['key', 'title', 'subtitle', 'source', 'settings', 'is_active', 'sort_order'])]
#[UseFactory(HomepageSectionFactory::class)]
class HomepageSection extends Model
{
    /** @use HasFactory<HomepageSectionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => HomepageSectionSource::class,
            'settings' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<HomepageSectionItem, $this>
     */
    public function homepageSectionItems(): HasMany
    {
        return $this->hasMany(HomepageSectionItem::class, 'homepage_section_id');
    }
}
