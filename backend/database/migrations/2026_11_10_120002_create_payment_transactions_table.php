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
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->string('type', 32);
            $table->string('status', 32);
            $table->string('provider_txn_id', 128)->nullable();
            $table->decimal('amount', 20, 6);
            $table->string('currency', 3);
            $table->json('raw_redacted')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unique(['payment_id', 'type', 'provider_txn_id'], 'uq_pt');
            $table->index(['type', 'created_at'], 'idx_pt_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
