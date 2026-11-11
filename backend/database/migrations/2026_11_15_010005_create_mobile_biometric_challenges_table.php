<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single-use, short-lived challenges for biometric re-authentication (OD-1, ADR-040). Each is
 * bound to one biometric key and to the access token that asked for it; only the SHA-256 of the
 * challenge string is stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_biometric_challenges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mobile_biometric_key_id')->constrained('mobile_biometric_keys')->cascadeOnDelete();
            $table->foreignId('access_token_id')->constrained('personal_access_tokens')->cascadeOnDelete();
            $table->char('challenge_hash', 64)->unique('uq_mbc_challenge_hash');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
            $table->index('expires_at', 'idx_mbc_expires');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_biometric_challenges');
    }
};
