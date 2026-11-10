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
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->string('name_snapshot', 255);
            $table->string('sku_snapshot', 80);
            $table->string('delivery_type', 32);
            $table->unsignedTinyInteger('quantity');
            $table->decimal('unit_price_base', 20, 6);
            $table->decimal('compare_at_base', 20, 6)->nullable();
            $table->decimal('discount_base', 20, 6)->default('0');
            $table->decimal('line_total_base', 20, 6);
            $table->decimal('estimated_unit_cost_base', 20, 6)->nullable();
            $table->decimal('custom_amount', 20, 6)->nullable();
            $table->json('pricing_breakdown');
            $table->timestamps();
            $table->index(['order_id'], 'idx_oi_order');
            $table->index(['product_id', 'created_at'], 'idx_oi_product');
            $table->index(['product_variant_id'], 'idx_oi_variant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
