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
        Schema::create('payment_eligibility_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->cascadeOnDelete();
            $table->string('effect', 32);
            $table->json('countries')->nullable();
            $table->json('ip_countries')->nullable();
            $table->boolean('require_ip_match')->default(false);
            $table->json('currencies')->nullable();
            $table->decimal('min_amount_base', 20, 6)->nullable();
            $table->decimal('max_amount_base', 20, 6)->nullable();
            $table->boolean('allow_guest')->default(true);
            $table->string('max_risk_level', 32)->nullable();
            $table->integer('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['payment_gateway_id', 'is_active', 'priority'], 'idx_per');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_eligibility_rules');
    }
};
