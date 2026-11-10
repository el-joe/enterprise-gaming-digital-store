<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Shared\Enums\EnvironmentMode;
use Database\Factories\Suppliers\SupplierCredentialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('supplier_credentials')]
#[Fillable(['supplier_id', 'mode', 'credentials', 'webhook_secret', 'rotated_at'])]
#[UseFactory(SupplierCredentialFactory::class)]
#[Hidden(['credentials', 'webhook_secret'])]
class SupplierCredential extends Model
{
    /** @use HasFactory<SupplierCredentialFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => EnvironmentMode::class,
            'credentials' => 'encrypted:array',
            'webhook_secret' => 'encrypted',
            'rotated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
