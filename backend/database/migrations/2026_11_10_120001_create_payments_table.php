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
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->restrictOnDelete();
            $table->unsignedTinyInteger('attempt_no');
            $table->string('status', 32);
            $table->string('method_code', 64)->nullable();
            $table->string('provider_payment_id', 128)->nullable();
            $table->string('provider_order_id', 128)->nullable();
            $table->string('provider_capture_id', 128)->nullable();
            $table->string('provider_reference', 128)->nullable();
            $table->decimal('base_amount', 20, 6);
            $table->char('base_currency', 3);
            $table->decimal('rate', 24, 12);
            $table->decimal('amount', 20, 6);
            $table->char('currency', 3);
            $table->decimal('fee_amount', 20, 6)->nullable();
            $table->string('fee_currency', 3)->nullable();
            $table->boolean('fee_is_estimate')->default(true);
            $table->decimal('settled_amount', 20, 6)->nullable();
            $table->string('settled_currency', 3)->nullable();
            $table->string('failure_code', 64)->nullable();
            $table->string('failure_message', 255)->nullable();
            $table->string('card_country', 2)->nullable();
            $table->char('card_fingerprint_hash', 64)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('next_check_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['public_id']);
            $table->unique(['order_id', 'attempt_no'], 'uq_pay_attempt');
            $table->unique(['payment_gateway_id', 'provider_payment_id'], 'uq_pay_provider');
            $table->unique(['payment_gateway_id', 'provider_capture_id'], 'uq_pay_capture');
            $table->index(['status', 'next_check_at'], 'idx_pay_reconcile');
            $table->index(['payment_gateway_id', 'status', 'created_at'], 'idx_pay_gw_time');
            $table->index(['order_id', 'status'], 'idx_pay_order');
            $table->foreign('base_currency')->references('code')->on('currencies')->restrictOnDelete();
            $table->foreign('currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
