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
        Schema::create('mcp_pending_actions', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->foreignId('service_account_id')->constrained('service_accounts')->restrictOnDelete();
            $table->string('tool', 64);
            $table->json('arguments');
            $table->char('arguments_hash', 64);
            $table->unsignedTinyInteger('risk_tier');
            $table->string('summary', 1000);
            $table->string('status', 32);
            $table->string('approved_via', 32)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('executed_at')->nullable();
            $table->json('result')->nullable();
            $table->timestamps();
            $table->unique(['public_id']);
            $table->index(['status', 'expires_at'], 'idx_mpa');
            $table->index(['service_account_id', 'arguments_hash', 'status'], 'idx_mpa_dedupe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_pending_actions');
    }
};
