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
        Schema::create('exchange_rates', function (Blueprint $table): void {
            $table->id();
            $table->char('base_currency', 3);
            $table->char('quote_currency', 3);
            $table->decimal('provider_rate', 24, 12)->nullable();
            $table->decimal('manual_rate', 24, 12)->nullable();
            $table->boolean('is_manual_active')->default(false);
            $table->timestamp('manual_expires_at')->nullable();
            $table->decimal('effective_rate', 24, 12);
            $table->string('source', 64);
            $table->timestamp('fetched_at');
            $table->decimal('pending_rate', 24, 12)->nullable();
            $table->timestamp('pending_since')->nullable();
            $table->timestamps();
            $table->unique(['base_currency', 'quote_currency'], 'uq_er');
            $table->foreign('base_currency')->references('code')->on('currencies')->restrictOnDelete();
            $table->foreign('quote_currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
