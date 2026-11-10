<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guest order tokens of stateless clients (M-A3, ADR-036): an opaque bearer value scoped to
     * one guest order, stored only as a SHA-256 hash. It replaces the browser session's order list
     * (`source` placement or otp) and carries the same proofs the session holds for a guest reveal
     * (`link_verified_at` for the signed delivery link, `otp_verified_at` for the email code).
     */
    public function up(): void
    {
        Schema::create('guest_order_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique('uq_got_token_hash');
            $table->string('source', 16);
            $table->timestamp('link_verified_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index('order_id', 'idx_got_order');
            $table->index('expires_at', 'idx_got_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_order_tokens');
    }
};
