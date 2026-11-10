<?php

namespace App\Domains\Seo\Models;

use App\Domains\Cms\Models\Media;
use Database\Factories\Seo\SeoMetadataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('seo_metadata')]
#[Fillable(['seoable_type', 'seoable_id', 'locale', 'title', 'description', 'canonical_override', 'robots', 'og_image_media_id', 'schema_overrides'])]
#[UseFactory(SeoMetadataFactory::class)]
class SeoMetadata extends Model
{
    /** @use HasFactory<SeoMetadataFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seoable_id' => 'integer',
            'schema_overrides' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function ogImageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_media_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo('seoable', 'seoable_type', 'seoable_id');
    }
}
