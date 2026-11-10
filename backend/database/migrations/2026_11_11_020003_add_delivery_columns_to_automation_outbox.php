<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M17: `automation:flush-outbox` records why the last delivery to n8n failed (HTTP status and a
 * short reason, never the response body) and when an event was given up as failed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('automation_outbox', function (Blueprint $table): void {
            $table->unsignedSmallInteger('last_http_status')->nullable()->after('attempts');
            $table->string('last_error', 500)->nullable()->after('last_http_status');
            $table->timestamp('failed_at')->nullable()->after('delivered_at');
            $table->index(['status', 'failed_at'], 'idx_ao_failed');
        });
    }

    public function down(): void
    {
        Schema::table('automation_outbox', function (Blueprint $table): void {
            $table->dropIndex('idx_ao_failed');
            $table->dropColumn(['last_http_status', 'last_error', 'failed_at']);
        });
    }
};
