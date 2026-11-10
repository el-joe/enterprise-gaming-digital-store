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
        Schema::create('daily_sales_summaries', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('dimension', 32);
            $table->string('dimension_key', 64);
            $table->unsignedInteger('orders_count');
            $table->unsignedInteger('units_count');
            $table->decimal('revenue_base', 20, 6);
            $table->decimal('refunds_base', 20, 6);
            $table->decimal('supplier_cost_base', 20, 6);
            $table->decimal('gateway_fee_base', 20, 6);
            $table->decimal('gross_profit_base', 20, 6);
            $table->decimal('net_profit_base', 20, 6);
            $table->timestamps();
            $table->unique(['date', 'dimension', 'dimension_key'], 'uq_dss');
            $table->index(['dimension', 'date'], 'idx_dss_dim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_sales_summaries');
    }
};
