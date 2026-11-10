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
        Schema::create('payment_gateway_currencies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->cascadeOnDelete();
            $table->char('currency', 3);
            $table->unsignedTinyInteger('minor_exponent');
            $table->decimal('min_amount', 20, 6)->nullable();
            $table->decimal('max_amount', 20, 6)->nullable();
            $table->boolean('is_enabled');
            $table->string('integration_ref', 64)->nullable();
            $table->timestamps();
            $table->unique(['payment_gateway_id', 'currency'], 'uq_pgcur');
            $table->foreign('currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_currencies');
    }
};
