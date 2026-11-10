<?php

namespace App\Domains\Cms\Models;

use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Identity\Models\User;
use Database\Factories\Cms\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('media')]
#[Fillable(['disk', 'path', 'original_filename', 'mime', 'size_bytes', 'width', 'height', 'alt', 'conversions', 'folder', 'source', 'license_note', 'uploaded_by'])]
#[UseFactory(MediaFactory::class)]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * Get the columns that receive a ULID on creation.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'conversions' => 'array',
            'source' => MediaSource::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
