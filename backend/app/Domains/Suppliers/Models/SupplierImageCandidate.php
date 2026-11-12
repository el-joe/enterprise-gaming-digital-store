<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Cms\Models\Media;
use App\Domains\Suppliers\Enums\SupplierImageKind;
use Database\Factories\Suppliers\SupplierImageCandidateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One supplier's image for a product, platform or category (uq_sic_subject_supplier_kind). The
 * subject's own image field is the active image; SupplierImageCandidates activates candidates.
 */
#[Table('supplier_image_candidates')]
#[Fillable(['subject_type', 'subject_id', 'supplier_id', 'kind', 'source_url', 'image_hash', 'media_id', 'fetched_at', 'error'])]
#[UseFactory(SupplierImageCandidateFactory::class)]
class SupplierImageCandidate extends Model
{
    /** @use HasFactory<SupplierImageCandidateFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => SupplierImageKind::class,
            'fetched_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
