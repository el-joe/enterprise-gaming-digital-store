<?php

namespace App\Domains\Affiliates\Models;

use App\Domains\Affiliates\Enums\AffiliateCommissionStatus;
use App\Domains\Orders\Models\Order;
use Database\Factories\Affiliates\AffiliateCommissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('affiliate_commissions')]
#[Fillable(['affiliate_id', 'order_id', 'payout_id'])]
#[UseFactory(AffiliateCommissionFactory::class)]
class AffiliateCommission extends Model
{
    /** @use HasFactory<AffiliateCommissionFactory> */
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
            'status' => AffiliateCommissionStatus::class,
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
     * @return BelongsTo<AffiliatePayout, $this>
     */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(AffiliatePayout::class, 'payout_id');
    }
}
