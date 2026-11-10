<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M17: an n8n retry of POST /suggestions must not queue the same draft twice. The hash covers the
 * workflow, type, subject and content.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_suggestions', function (Blueprint $table): void {
            $table->char('dedupe_hash', 64)->nullable()->after('content');
            $table->unique(['dedupe_hash'], 'uq_ais_dedupe');
        });
    }

    public function down(): void
    {
        Schema::table('ai_suggestions', function (Blueprint $table): void {
            $table->dropUnique('uq_ais_dedupe');
            $table->dropColumn('dedupe_hash');
        });
    }
};
