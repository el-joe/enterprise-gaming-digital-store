<?php

namespace App\Domains\Customers\Models;

use App\Domains\Customers\Enums\CustomerConsentChannel;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Customers\CustomerConsentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('customer_consents')]
#[Fillable(['email', 'channel', 'granted', 'source', 'ip'])]
#[UseFactory(CustomerConsentFactory::class)]
class CustomerConsent extends Model
{
    /** @use HasFactory<CustomerConsentFactory> */
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
            'channel' => CustomerConsentChannel::class,
            'granted' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
