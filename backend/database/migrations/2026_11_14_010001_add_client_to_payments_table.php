<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The client channel of a payment attempt (M-A6, ADR-039): `web` or `app`. It selects the
     * server-built return and cancel URLs sent to the provider. Existing attempts were all
     * started by the browser, so the default is `web`.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('client', 8)->default('web')->after('method_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn('client');
        });
    }
};
