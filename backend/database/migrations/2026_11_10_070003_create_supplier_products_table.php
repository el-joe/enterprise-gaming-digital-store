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
        Schema::create('supplier_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('external_id', 128);
            $table->string('name', 255);
            $table->string('brand_name', 190)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('category_name', 190)->nullable();
            $table->string('denomination_type', 32)->nullable();
            $table->string('currency', 3)->nullable();
            $table->json('fixed_denominations')->nullable();
            $table->decimal('min_denomination', 20, 6)->nullable();
            $table->decimal('max_denomination', 20, 6)->nullable();
            $table->string('sender_currency', 3)->nullable();
            $table->json('cost_map')->nullable();
            $table->decimal('discount_pct', 7, 4)->nullable();
            $table->decimal('fee_fixed', 20, 6)->nullable();
            $table->decimal('fee_pct', 7, 4)->nullable();
            $table->boolean('supports_preorder')->default(false);
            $table->boolean('requires_user_id')->default(false);
            $table->string('availability', 32)->default('unknown');
            $table->json('raw')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();
            $table->unique(['supplier_id', 'external_id'], 'uq_spd');
            $table->index(['supplier_id', 'brand_name'], 'idx_spd_name');
            $table->index(['supplier_id', 'removed_at'], 'idx_spd_removed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
