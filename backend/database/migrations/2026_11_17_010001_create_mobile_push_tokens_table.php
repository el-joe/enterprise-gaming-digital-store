<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Push tokens of the mobile app (M-A18, owner decision 9, ADR-042). One token per sign-in
 * (refresh-token family, ADR-035): registering again replaces it, and logout, sign-out
 * everywhere, device revocation, refresh-token reuse and a password change delete the rows of the
 * revoked families. The FCM registration token is stored encrypted; `token_hash` (SHA-256) keeps
 * one row per token, so a device that signs in to another account moves its token there.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_push_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('family_id', 26)->unique('uq_mpt_family');
            $table->string('platform', 16);
            $table->text('token');
            $table->char('token_hash', 64)->unique('uq_mpt_token_hash');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
            $table->index('user_id', 'idx_mpt_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_push_tokens');
    }
};
