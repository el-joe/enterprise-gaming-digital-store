<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How the recent authentication on a bearer token was given (OD-1, ADR-040): `password` or
 * `biometric`. Both satisfy a code reveal; only `password` may register a biometric key, so a
 * biometric confirmation can never enrol another key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->string('recent_auth_method', 16)->nullable()->after('password_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->dropColumn('recent_auth_method');
        });
    }
};
