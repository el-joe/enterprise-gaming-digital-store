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
        Schema::create('coupon_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->restrictOnDelete();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->char('email_hash', 64)->nullable();
            $table->decimal('discount_base', 20, 6);
            $table->string('status', 32);
            $table->timestamps();
            $table->unique(['coupon_id', 'order_id'], 'uq_cr');
            $table->index(['coupon_id', 'user_id', 'status'], 'idx_cr_user');
            $table->index(['coupon_id', 'status', 'created_at'], 'idx_cr_perf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
    }
};
