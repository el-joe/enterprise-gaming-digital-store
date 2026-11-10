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
        Schema::create('supplier_product_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_product_id')->constrained('supplier_products')->cascadeOnDelete();
            $table->decimal('denomination', 20, 6);
            $table->decimal('cost', 20, 6);
            $table->string('cost_currency', 3);
            $table->timestamp('captured_at');
            $table->timestamp('created_at')->nullable();
            $table->index(['supplier_product_id', 'denomination', 'captured_at'], 'idx_spp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_product_prices');
    }
};
