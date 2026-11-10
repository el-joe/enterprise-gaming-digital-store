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
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->string('order_number', 24);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_email', 190)->nullable();
            $table->timestamp('guest_email_verified_at')->nullable();
            $table->string('contact_email', 190);
            $table->string('contact_phone', 32)->nullable();
            $table->string('customer_name', 120)->nullable();
            $table->string('status', 32);
            $table->timestamp('status_changed_at');
            $table->char('country_code', 2);
            $table->string('ip_country', 2)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('locale', 8);
            $table->string('ga_client_id', 64)->nullable();
            $table->char('base_currency', 3);
            $table->decimal('subtotal_base', 20, 6);
            $table->decimal('discount_base', 20, 6)->default('0');
            $table->decimal('tax_base', 20, 6)->default('0');
            $table->decimal('total_base', 20, 6);
            $table->char('display_currency', 3);
            $table->decimal('display_rate', 24, 12);
            $table->decimal('display_total', 20, 6);
            $table->char('payment_currency', 3)->nullable();
            $table->decimal('payment_rate', 24, 12)->nullable();
            $table->decimal('payment_total', 20, 6)->nullable();
            $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->restrictOnDelete();
            $table->foreignId('exchange_rate_history_id')->nullable()->constrained('exchange_rate_history')->nullOnDelete();
            $table->string('rate_source', 64)->nullable();
            $table->timestamp('rate_fetched_at')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 64)->nullable();
            $table->unsignedBigInteger('affiliate_referral_id')->nullable();
            $table->unsignedBigInteger('primary_payment_id')->nullable();
            $table->smallInteger('risk_score')->nullable();
            $table->boolean('has_open_dispute')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->char('placement_hash', 64)->nullable();
            $table->timestamps();
            $table->unique(['public_id'], 'uq_o_public');
            $table->unique(['order_number'], 'uq_o_number');
            $table->unique(['primary_payment_id'], 'uq_o_primary_payment');
            $table->unique(['placement_hash'], 'uq_o_placement');
            $table->index(['user_id', 'created_at'], 'idx_o_user');
            $table->index(['guest_email', 'created_at'], 'idx_o_guest');
            $table->index(['status', 'status_changed_at'], 'idx_o_status');
            $table->index(['status', 'expires_at'], 'idx_o_expire');
            $table->index(['paid_at', 'payment_gateway_id'], 'idx_o_paid');
            $table->index(['country_code', 'paid_at'], 'idx_o_country');
            $table->index(['payment_currency', 'paid_at'], 'idx_o_payment_currency');
            $table->index(['affiliate_referral_id'], 'idx_o_affiliate_referral');
            $table->foreign('country_code')->references('code')->on('countries')->restrictOnDelete();
            $table->foreign('base_currency')->references('code')->on('currencies')->restrictOnDelete();
            $table->foreign('display_currency')->references('code')->on('currencies')->restrictOnDelete();
            $table->foreign('payment_currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
