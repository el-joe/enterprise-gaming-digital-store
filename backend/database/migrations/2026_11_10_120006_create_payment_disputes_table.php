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
        Schema::create('payment_disputes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->string('provider_dispute_id', 128);
            $table->string('status', 32);
            $table->string('reason', 128)->nullable();
            $table->decimal('amount', 20, 6);
            $table->string('currency', 3);
            $table->timestamp('evidence_due_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['payment_id', 'provider_dispute_id'], 'uq_pd');
            $table->index(['status', 'evidence_due_at'], 'idx_pd_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_disputes');
    }
};
