<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('digital_codes', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->string('source', 32);
            $table->foreignId('order_item_unit_id')->nullable()->constrained('order_item_units')->nullOnDelete();
            $table->foreignId('supplier_transaction_id')->nullable()->constrained('supplier_transactions')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->restrictOnDelete();
            $table->string('stock_status', 32)->nullable();
            $table->mediumText('code_ciphertext');
            $table->text('pin_ciphertext')->nullable();
            $table->text('redemption_url_ciphertext')->nullable();
            $table->unsignedSmallInteger('key_version');
            $table->char('code_hash', 64);
            $table->string('masked_code', 32);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('import_batch', 64)->nullable();
            $table->timestamps();
            $table->unique(['public_id']);
            $table->unique(['code_hash'], 'uq_dc_hash');
            $table->unique(['order_item_unit_id'], 'uq_dc_unit');
            $table->index(['product_variant_id', 'stock_status', 'id'], 'idx_dc_stock');
            $table->index(['key_version'], 'idx_dc_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_codes');
    }
};
