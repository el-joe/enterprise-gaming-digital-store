<?php

namespace App\Domains\Fulfillment\Models;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Enums\DigitalCodeStockStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Database\Factories\Fulfillment\DigitalCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('digital_codes')]
#[Fillable(['source', 'order_item_unit_id', 'supplier_transaction_id', 'product_variant_id', 'masked_code', 'expires_at', 'imported_by', 'import_batch'])]
#[UseFactory(DigitalCodeFactory::class)]
#[Hidden(['code_ciphertext', 'pin_ciphertext', 'redemption_url_ciphertext', 'code_hash'])]
class DigitalCode extends Model
{
    /** @use HasFactory<DigitalCodeFactory> */
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
            'source' => DigitalCodeSource::class,
            'stock_status' => DigitalCodeStockStatus::class,
            'key_version' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<OrderItemUnit, $this>
     */
    public function orderItemUnit(): BelongsTo
    {
        return $this->belongsTo(OrderItemUnit::class, 'order_item_unit_id');
    }

    /**
     * @return BelongsTo<SupplierTransaction, $this>
     */
    public function supplierTransaction(): BelongsTo
    {
        return $this->belongsTo(SupplierTransaction::class, 'supplier_transaction_id');
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * @return HasMany<DigitalCodeAccess, $this>
     */
    public function digitalCodeAccesses(): HasMany
    {
        return $this->hasMany(DigitalCodeAccess::class, 'digital_code_id');
    }
}
