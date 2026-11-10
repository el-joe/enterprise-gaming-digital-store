<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\AiSuggestionStatus;
use App\Domains\Automation\Enums\AiSuggestionType;
use App\Domains\Identity\Models\User;
use Database\Factories\Automation\AiSuggestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('ai_suggestions')]
#[Fillable(['workflow_id', 'type', 'subject_type', 'subject_id', 'content', 'review_note'])]
#[UseFactory(AiSuggestionFactory::class)]
class AiSuggestion extends Model
{
    /** @use HasFactory<AiSuggestionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AiSuggestionType::class,
            'subject_id' => 'integer',
            'content' => 'array',
            'status' => AiSuggestionStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }
}
