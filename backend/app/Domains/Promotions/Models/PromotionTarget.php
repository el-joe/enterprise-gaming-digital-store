<?php

namespace App\Domains\Promotions\Models;

use App\Domains\Promotions\Enums\PromotionTargetTargetType;
use Database\Factories\Promotions\PromotionTargetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('promotion_targets')]
#[Fillable(['promotion_id', 'target_type', 'target_id'])]
#[UseFactory(PromotionTargetFactory::class)]
class PromotionTarget extends Model
{
    /** @use HasFactory<PromotionTargetFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_type' => PromotionTargetTargetType::class,
            'target_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Promotion, $this>
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
