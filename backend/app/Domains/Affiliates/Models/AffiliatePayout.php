<?php

namespace App\Domains\Affiliates\Models;

use App\Domains\Affiliates\Enums\AffiliatePayoutStatus;
use App\Domains\Identity\Models\User;
use Database\Factories\Affiliates\AffiliatePayoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('affiliate_payouts')]
#[Fillable(['affiliate_id', 'reference', 'created_by'])]
#[UseFactory(AffiliatePayoutFactory::class)]
class AffiliatePayout extends Model
{
    /** @use HasFactory<AffiliatePayoutFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_base' => 'decimal:6',
            'status' => AffiliatePayoutStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Affiliate, $this>
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<AffiliateCommission, $this>
     */
    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'payout_id');
    }
}
