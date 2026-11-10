<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pending two-factor sign-ins of the mobile app (M-A2, ADR-035): replaces the web session's
     * pending state. Only the SHA-256 hash of the challenge token is stored.
     */
    public function up(): void
    {
        Schema::create('mobile_login_challenges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique('uq_mlc_token_hash');
            $table->string('device_name', 100);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
            $table->index('expires_at', 'idx_mlc_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_login_challenges');
    }
};
