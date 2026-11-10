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
        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->restrictOnDelete();
            $table->string('sku', 80);
            $table->string('title', 190);
            $table->decimal('denomination_value', 20, 6)->nullable();
            $table->char('denomination_currency', 3)->nullable();
            $table->boolean('is_custom_amount')->default(false);
            $table->decimal('custom_min', 20, 6)->nullable();
            $table->decimal('custom_max', 20, 6)->nullable();
            $table->decimal('manual_cost_base', 20, 6)->nullable();
            $table->decimal('price_base', 20, 6)->nullable();
            $table->decimal('compare_at_base', 20, 6)->nullable();
            $table->string('availability', 32)->default('out_of_stock');
            $table->timestamp('pricing_dirty_at')->nullable();
            $table->boolean('is_active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['public_id']);
            $table->unique(['sku']);
            $table->unique(['product_id', 'region_id', 'denomination_value', 'denomination_currency'], 'uq_pv_product_region_denomination');
            $table->index(['product_id', 'is_active', 'sort_order'], 'idx_pv_product');
            $table->index(['is_active', 'availability', 'price_base'], 'idx_pv_price');
            $table->index(['pricing_dirty_at'], 'idx_pv_dirty');
            $table->index(['region_id', 'is_active'], 'idx_pv_region');
            $table->foreign('denomination_currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
