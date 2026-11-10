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
        Schema::create('order_item_financials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete();
            $table->string('country_code', 2);
            $table->string('currency', 3);
            $table->decimal('revenue_base', 20, 6);
            $table->decimal('supplier_cost_base', 20, 6);
            $table->decimal('gateway_fee_base', 20, 6);
            $table->boolean('gateway_fee_is_estimate');
            $table->decimal('fx_cost_base', 20, 6);
            $table->decimal('other_cost_base', 20, 6);
            $table->decimal('refunded_base', 20, 6)->default('0');
            $table->decimal('gross_profit_base', 20, 6);
            $table->decimal('net_profit_base', 20, 6);
            $table->timestamp('recognized_at');
            $table->timestamps();
            $table->unique(['order_item_id']);
            $table->index(['recognized_at'], 'idx_oif_recognized');
            $table->index(['product_id', 'recognized_at'], 'idx_oif_product');
            $table->index(['supplier_id', 'recognized_at'], 'idx_oif_supplier');
            $table->index(['payment_gateway_id', 'recognized_at'], 'idx_oif_gateway');
            $table->index(['country_code', 'recognized_at'], 'idx_oif_country');
            $table->index(['currency', 'recognized_at'], 'idx_oif_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_financials');
    }
};
