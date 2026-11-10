<?php

namespace App\Domains\Promotions\Models;

use App\Domains\Promotions\Enums\PromotionType;
use Database\Factories\Promotions\PromotionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('promotions')]
#[Fillable(['name', 'campaign', 'type', 'value', 'priority', 'stackable', 'badge_text', 'starts_at', 'ends_at', 'is_active'])]
#[UseFactory(PromotionFactory::class)]
class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PromotionType::class,
            'value' => 'decimal:6',
            'priority' => 'integer',
            'stackable' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PromotionTarget, $this>
     */
    public function promotionTargets(): HasMany
    {
        return $this->hasMany(PromotionTarget::class, 'promotion_id');
    }
}
