<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Device-bound biometric keys of the mobile app (OD-1, ADR-040). One live key per sign-in
 * (refresh-token family): the private key never leaves the device's secure hardware and is
 * unlocked by the OS biometric prompt; only the public key is stored here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_biometric_keys', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique('uq_mbk_public_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('family_id', 26);
            $table->string('algorithm', 16);
            $table->text('public_key');
            $table->char('key_fingerprint', 64);
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason', 32)->nullable();
            $table->timestamps();
            $table->index(['family_id', 'revoked_at'], 'idx_mbk_family');
            $table->index(['user_id', 'revoked_at'], 'idx_mbk_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_biometric_keys');
    }
};
