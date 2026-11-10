<?php

namespace App\Domains\Wallet\Models;

use App\Domains\Currency\Models\Currency;
use App\Domains\Identity\Models\User;
use App\Domains\Wallet\Enums\CustomerWalletStatus;
use Database\Factories\Wallet\CustomerWalletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('customer_wallets')]
#[Fillable(['currency'])]
#[UseFactory(CustomerWalletFactory::class)]
class CustomerWallet extends Model
{
    /** @use HasFactory<CustomerWalletFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:6',
            'status' => CustomerWalletStatus::class,
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
    public function currencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    /**
     * @return HasMany<WalletTransaction, $this>
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'customer_wallet_id');
    }
}
