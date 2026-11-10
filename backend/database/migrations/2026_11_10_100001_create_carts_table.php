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
        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->char('session_token', 64)->nullable();
            $table->string('email', 190)->nullable();
            $table->char('display_currency', 3);
            $table->char('country_code', 2)->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('status', 32)->default('active');
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('last_activity_at');
            $table->timestamps();
            $table->unique(['public_id']);
            $table->index(['user_id', 'status'], 'idx_c_user');
            $table->index(['session_token', 'status'], 'idx_c_session');
            $table->index(['status', 'last_activity_at'], 'idx_c_abandon');
            $table->foreign('display_currency')->references('code')->on('currencies')->restrictOnDelete();
            $table->foreign('country_code')->references('code')->on('countries')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
