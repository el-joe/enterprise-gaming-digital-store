<?php

namespace App\Domains\Affiliates\Models;

use App\Domains\Affiliates\Enums\AffiliateStatus;
use App\Domains\Identity\Models\User;
use Database\Factories\Affiliates\AffiliateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('affiliates')]
#[Fillable(['code', 'commission_pct', 'payout_details', 'notes'])]
#[UseFactory(AffiliateFactory::class)]
#[Hidden(['payout_details'])]
class Affiliate extends Model
{
    /** @use HasFactory<AffiliateFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AffiliateStatus::class,
            'commission_pct' => 'decimal:4',
            'payout_details' => 'encrypted:array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<AffiliateReferral, $this>
     */
    public function affiliateReferrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class, 'affiliate_id');
    }

    /**
     * @return HasMany<AffiliateCommission, $this>
     */
    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'affiliate_id');
    }

    /**
     * @return HasMany<AffiliatePayout, $this>
     */
    public function affiliatePayouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class, 'affiliate_id');
    }
}
