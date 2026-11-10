<?php

namespace App\Domains\Wallet\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use App\Domains\Wallet\Enums\WalletTransactionType;
use Database\Factories\Wallet\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('wallet_transactions')]
#[Fillable(['customer_wallet_id', 'type', 'reference_type', 'reference_id', 'idempotency_key', 'note', 'created_by'])]
#[UseFactory(WalletTransactionFactory::class)]
class WalletTransaction extends Model
{
    /** @use HasFactory<WalletTransactionFactory> */
    use AppendOnly, HasFactory;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => WalletTransactionType::class,
            'amount' => 'decimal:6',
            'balance_after' => 'decimal:6',
            'reference_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CustomerWallet, $this>
     */
    public function customerWallet(): BelongsTo
    {
        return $this->belongsTo(CustomerWallet::class, 'customer_wallet_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}
