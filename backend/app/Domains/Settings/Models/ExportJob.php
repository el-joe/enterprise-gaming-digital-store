<?php

namespace App\Domains\Settings\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\ExportJobStatus;
use Database\Factories\Settings\ExportJobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('export_jobs')]
#[Fillable(['table_key', 'filters', 'media_path', 'row_count', 'expires_at', 'error'])]
#[UseFactory(ExportJobFactory::class)]
class ExportJob extends Model
{
    /** @use HasFactory<ExportJobFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'status' => ExportJobStatus::class,
            'row_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
