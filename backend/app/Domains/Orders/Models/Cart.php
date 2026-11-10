<?php

namespace App\Domains\Orders\Models;

use App\Domains\Currency\Models\Currency;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Shared\Models\Country;
use Database\Factories\Orders\CartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('carts')]
#[Fillable(['session_token', 'email', 'display_currency', 'country_code', 'coupon_id', 'abandoned_at', 'reminder_sent_at', 'last_activity_at'])]
#[UseFactory(CartFactory::class)]
#[Hidden(['session_token'])]
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
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
            'status' => CartStatus::class,
            'abandoned_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'last_activity_at' => 'datetime',
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
     * @return BelongsTo<Currency, $this>
     */
    public function displayCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'display_currency', 'code');
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }
}
