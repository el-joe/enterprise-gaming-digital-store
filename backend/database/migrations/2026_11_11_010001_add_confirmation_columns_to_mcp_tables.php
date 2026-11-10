<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M16: the two-step write confirmation (MCP_SERVER.md section 5). A pending action remembers the
 * Sanctum token that requested it and, for a tier 1 action on an account holding
 * `actions:confirm`, the SHA-256 hash of a single-use confirmation token. The hash is cleared
 * when the token is used, so it can never be replayed. Audit rows carry the request id and the
 * error code returned to the client.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mcp_pending_actions', function (Blueprint $table): void {
            $table->unsignedBigInteger('requested_token_id')->nullable()->after('service_account_id');
            $table->char('confirmation_hash', 64)->nullable()->after('arguments_hash');
            $table->timestamp('decided_at')->nullable()->after('approved_by');
            $table->string('decision_note', 1000)->nullable()->after('decided_at');
        });

        Schema::table('mcp_audit_logs', function (Blueprint $table): void {
            $table->string('error_code', 64)->nullable()->after('outcome');
            $table->char('request_id', 26)->nullable()->after('ip');
        });
    }

    public function down(): void
    {
        Schema::table('mcp_audit_logs', function (Blueprint $table): void {
            $table->dropColumn(['error_code', 'request_id']);
        });

        Schema::table('mcp_pending_actions', function (Blueprint $table): void {
            $table->dropColumn(['requested_token_id', 'confirmation_hash', 'decided_at', 'decision_note']);
        });
    }
};
