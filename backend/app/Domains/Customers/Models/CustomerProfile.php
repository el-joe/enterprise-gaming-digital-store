<?php

namespace App\Domains\Customers\Models;

use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Identity\Models\User;
use Database\Factories\Customers\CustomerProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('customer_profiles')]
#[Fillable(['risk_level', 'risk_notes', 'first_order_at', 'last_order_at'])]
#[UseFactory(CustomerProfileFactory::class)]
class CustomerProfile extends Model
{
    /** @use HasFactory<CustomerProfileFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'risk_level' => CustomerRiskLevel::class,
            'orders_count' => 'integer',
            'lifetime_value_base' => 'decimal:6',
            'first_order_at' => 'datetime',
            'last_order_at' => 'datetime',
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
