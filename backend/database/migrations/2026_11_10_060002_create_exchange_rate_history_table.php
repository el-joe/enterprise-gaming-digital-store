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
        Schema::create('exchange_rate_history', function (Blueprint $table): void {
            $table->id();
            $table->string('base_currency', 3);
            $table->string('quote_currency', 3);
            $table->decimal('provider_rate', 24, 12)->nullable();
            $table->decimal('effective_rate', 24, 12);
            $table->string('source', 64);
            $table->boolean('is_manual');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('captured_at');
            $table->timestamp('created_at')->nullable();
            $table->index(['quote_currency', 'captured_at'], 'idx_erh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_history');
    }
};
