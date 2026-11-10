<?php

namespace App\Domains\Orders\Models;

use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Affiliates\Models\AffiliateReferral;
use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRateHistory;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\Refund;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\CouponRedemption;
use App\Domains\Shared\Models\Country;
use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Models\SupportTicket;
use Database\Factories\Orders\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table('orders')]
#[Fillable(['order_number', 'guest_email', 'guest_email_verified_at', 'contact_email', 'contact_phone', 'customer_name', 'country_code', 'ip_country', 'ip_address', 'user_agent', 'locale', 'ga_client_id', 'base_currency', 'display_currency', 'payment_currency', 'payment_gateway_id', 'exchange_rate_history_id', 'rate_source', 'rate_fetched_at', 'coupon_id', 'coupon_code', 'affiliate_referral_id', 'expires_at'])]
#[UseFactory(OrderFactory::class)]
#[Hidden(['placement_hash'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, HasUlids;

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
            'guest_email_verified_at' => 'datetime',
            'status' => OrderStatus::class,
            'status_changed_at' => 'datetime',
            'subtotal_base' => 'decimal:6',
            'discount_base' => 'decimal:6',
            'tax_base' => 'decimal:6',
            'total_base' => 'decimal:6',
            'display_rate' => 'decimal:12',
            'display_total' => 'decimal:6',
            'payment_rate' => 'decimal:12',
            'payment_total' => 'decimal:6',
            'rate_fetched_at' => 'datetime',
            'risk_score' => 'integer',
            'has_open_dispute' => 'boolean',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
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

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function baseCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function displayCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'display_currency', 'code');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function paymentCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'payment_currency', 'code');
    }

    /**
     * @return BelongsTo<PaymentGateway, $this>
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    /**
     * @return BelongsTo<ExchangeRateHistory, $this>
     */
    public function exchangeRateHistory(): BelongsTo
    {
        return $this->belongsTo(ExchangeRateHistory::class, 'exchange_rate_history_id');
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * @return BelongsTo<AffiliateReferral, $this>
     */
    public function affiliateReferral(): BelongsTo
    {
        return $this->belongsTo(AffiliateReferral::class, 'affiliate_referral_id');
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function primaryPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'primary_payment_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * @return HasMany<OrderItemUnit, $this>
     */
    public function orderItemUnits(): HasMany
    {
        return $this->hasMany(OrderItemUnit::class, 'order_id');
    }

    /**
     * @return HasMany<OrderEvent, $this>
     */
    public function orderEvents(): HasMany
    {
        return $this->hasMany(OrderEvent::class, 'order_id');
    }

    /**
     * @return HasMany<OrderNote, $this>
     */
    public function orderNotes(): HasMany
    {
        return $this->hasMany(OrderNote::class, 'order_id');
    }

    /**
     * @return HasOne<RiskAssessment, $this>
     */
    public function riskAssessment(): HasOne
    {
        return $this->hasOne(RiskAssessment::class, 'order_id');
    }

    /**
     * @return HasMany<OrderItemFinancial, $this>
     */
    public function orderItemFinancials(): HasMany
    {
        return $this->hasMany(OrderItemFinancial::class, 'order_id');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    /**
     * @return HasMany<Refund, $this>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'order_id');
    }

    /**
     * @return HasMany<CouponRedemption, $this>
     */
    public function couponRedemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class, 'order_id');
    }

    /**
     * @return HasOne<AffiliateReferral, $this>
     */
    public function affiliateReferralRelated(): HasOne
    {
        return $this->hasOne(AffiliateReferral::class, 'order_id');
    }

    /**
     * @return HasMany<AffiliateCommission, $this>
     */
    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'order_id');
    }

    /**
     * @return HasMany<SupportTicket, $this>
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'order_id');
    }

    /**
     * @return HasMany<RefundRequest, $this>
     */
    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class, 'order_id');
    }

    /**
     * @return HasMany<AnalyticsEventDispatch, $this>
     */
    public function analyticsEventDispatches(): HasMany
    {
        return $this->hasMany(AnalyticsEventDispatch::class, 'order_id');
    }
}
