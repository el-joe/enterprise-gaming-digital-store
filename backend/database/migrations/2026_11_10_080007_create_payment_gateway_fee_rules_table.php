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
        Schema::create('payment_gateway_fee_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->cascadeOnDelete();
            $table->string('payment_method_code', 64)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->decimal('percent_fee', 7, 4)->default('0');
            $table->decimal('fixed_fee', 20, 6)->default('0');
            $table->string('fixed_fee_currency', 3)->nullable();
            $table->boolean('is_active');
            $table->timestamps();
            $table->index(['payment_gateway_id', 'is_active'], 'idx_pgfr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_fee_rules');
    }
};
