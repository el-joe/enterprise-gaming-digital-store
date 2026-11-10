<?php

namespace App\Domains\Orders\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\RiskAssessmentDecision;
use Database\Factories\Orders\RiskAssessmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('risk_assessments')]
#[Fillable(['order_id', 'score', 'decision', 'signals', 'review_note'])]
#[UseFactory(RiskAssessmentFactory::class)]
class RiskAssessment extends Model
{
    /** @use HasFactory<RiskAssessmentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'decision' => RiskAssessmentDecision::class,
            'signals' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
