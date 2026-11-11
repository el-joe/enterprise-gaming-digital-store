<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner decision 5 (OD-1): the app's `X-Device-Integrity` verdict (`ok`, `compromised`,
 * `unknown`) on a code reveal, null when not sent (web, older apps, staff).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_code_accesses', function (Blueprint $table): void {
            $table->string('device_integrity', 16)->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('digital_code_accesses', function (Blueprint $table): void {
            $table->dropColumn('device_integrity');
        });
    }
};
