<?php

namespace App\Domains\Affiliates\Models;

use App\Domains\Affiliates\Enums\AffiliateReferralStatus;
use App\Domains\Orders\Models\Order;
use Database\Factories\Affiliates\AffiliateReferralFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('affiliate_referrals')]
#[Fillable(['affiliate_id', 'visitor_token_hash', 'landing_url', 'order_id', 'clicked_at', 'converted_at'])]
#[UseFactory(AffiliateReferralFactory::class)]
#[Hidden(['visitor_token_hash'])]
class AffiliateReferral extends Model
{
    /** @use HasFactory<AffiliateReferralFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AffiliateReferralStatus::class,
            'clicked_at' => 'datetime',
            'converted_at' => 'datetime',
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
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'affiliate_referral_id');
    }
}
