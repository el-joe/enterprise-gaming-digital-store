<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rotating refresh tokens of the mobile app (M-A2, ADR-035). Only the SHA-256 hash of a token
     * is stored. Each row is single use; rotation inserts the next row of the same family.
     */
    public function up(): void
    {
        Schema::create('mobile_refresh_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('family_id', 26);
            $table->char('token_hash', 64)->unique('uq_mrt_token_hash');
            $table->string('device_name', 100);
            $table->foreignId('access_token_id')->nullable()->constrained('personal_access_tokens')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason', 32)->nullable();
            $table->timestamps();
            $table->index('family_id', 'idx_mrt_family');
            $table->index(['user_id', 'device_name'], 'idx_mrt_user_device');
            $table->index('expires_at', 'idx_mrt_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_refresh_tokens');
    }
};
