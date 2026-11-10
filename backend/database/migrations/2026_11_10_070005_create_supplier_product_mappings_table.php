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
        Schema::create('supplier_product_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('supplier_product_id')->constrained('supplier_products')->restrictOnDelete();
            $table->string('role', 32);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->decimal('supplier_unit_price', 20, 6);
            $table->string('supplier_unit_currency', 3);
            $table->decimal('override_cost', 20, 6)->nullable();
            $table->char('override_cost_currency', 3)->nullable();
            $table->json('allowed_countries')->nullable();
            $table->json('denied_countries')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('availability', 32)->default('unknown');
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
            $table->unique(['product_variant_id', 'supplier_id'], 'uq_spm');
            $table->index(['product_variant_id', 'is_active', 'priority'], 'idx_spm_select');
            $table->index(['supplier_id', 'is_active'], 'idx_spm_supplier');
            $table->foreign('override_cost_currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_product_mappings');
    }
};
