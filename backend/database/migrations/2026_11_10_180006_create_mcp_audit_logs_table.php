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
        Schema::create('mcp_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_account_id')->nullable()->constrained('service_accounts')->nullOnDelete();
            $table->unsignedBigInteger('token_id')->nullable();
            $table->string('tool', 64);
            $table->json('arguments_redacted')->nullable();
            $table->string('outcome', 32);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['service_account_id', 'created_at'], 'idx_mal_account');
            $table->index(['tool', 'outcome', 'created_at'], 'idx_mal_tool');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_audit_logs');
    }
};
