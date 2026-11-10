<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * M17: automation_logs holds two kinds of row. `execution` rows are the n8n execution log
 * (start and finish, one per workflow and execution key); `call` rows record every request to
 * /api/v1/automation, including refused ones. Duplicate-execution protection stays a unique key,
 * now over a stored generated column that is null for call rows, so many calls may share an
 * execution key while a second execution row cannot.
 *
 * Rolling back deletes the call rows, because the original unique key cannot hold them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('automation_logs', function (Blueprint $table): void {
            $table->string('kind', 16)->default('execution')->after('id');
            $table->foreignId('service_account_id')->nullable()->after('kind')->constrained('service_accounts')->nullOnDelete();
            $table->string('endpoint', 96)->nullable()->after('execution_key');
            $table->unsignedSmallInteger('http_status')->nullable()->after('endpoint');
            $table->string('error_code', 64)->nullable()->after('http_status');
            $table->unsignedInteger('duration_ms')->nullable()->after('error_code');
            $table->string('ip', 45)->nullable()->after('duration_ms');
            $table->char('request_id', 26)->nullable()->after('ip');
            $table->string('execution_unique_key', 128)->nullable()->storedAs("CASE WHEN kind = 'execution' THEN execution_key END");
        });

        Schema::table('automation_logs', function (Blueprint $table): void {
            $table->dropUnique('uq_alog');
        });

        Schema::table('automation_logs', function (Blueprint $table): void {
            $table->unique(['workflow_id', 'execution_unique_key'], 'uq_alog');
            $table->index(['kind', 'status', 'started_at'], 'idx_alog_kind_status');
        });
    }

    public function down(): void
    {
        DB::table('automation_logs')->where('kind', '!=', 'execution')->delete();

        Schema::table('automation_logs', function (Blueprint $table): void {
            $table->dropUnique('uq_alog');
            $table->dropIndex('idx_alog_kind_status');
        });

        Schema::table('automation_logs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('service_account_id');
            $table->dropColumn(['kind', 'endpoint', 'http_status', 'error_code', 'duration_ms', 'ip', 'request_id', 'execution_unique_key']);
        });

        Schema::table('automation_logs', function (Blueprint $table): void {
            $table->unique(['workflow_id', 'execution_key'], 'uq_alog');
        });
    }
};
