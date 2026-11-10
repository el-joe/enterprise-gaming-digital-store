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
        Schema::create('refunds', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('destination', 32);
            $table->string('status', 32);
            $table->string('reason', 32);
            $table->decimal('amount', 20, 6);
            $table->char('currency', 3);
            $table->decimal('amount_base', 20, 6);
            $table->string('provider_refund_id', 128)->nullable();
            $table->string('requested_by_type', 32);
            $table->unsignedBigInteger('requested_by_id')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['public_id']);
            $table->unique(['payment_id', 'provider_refund_id'], 'uq_ref_provider');
            $table->index(['status', 'created_at'], 'idx_ref_status');
            $table->index(['order_id'], 'idx_ref_order');
            $table->foreign('currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
