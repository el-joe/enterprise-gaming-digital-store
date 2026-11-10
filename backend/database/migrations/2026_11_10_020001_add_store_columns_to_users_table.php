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
        Schema::table('users', function (Blueprint $table): void {
            $table->ulid('public_id')->after('id')->unique();
            $table->string('type', 32)->default('customer')->after('name');
            $table->string('status', 32)->default('active')->after('type');
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('locale', 8)->nullable()->after('phone');
            $table->char('display_currency', 3)->nullable()->after('locale');
            $table->char('country_code', 2)->nullable()->after('display_currency');
            $table->text('two_factor_secret')->nullable()->after('remember_token');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_confirmed_at');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_recovery_codes');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->softDeletes();

            $table->index(['type', 'status'], 'idx_users_type_status');
            $table->index(['created_at'], 'idx_users_created');

            $table->foreign('display_currency')->references('code')->on('currencies')->restrictOnDelete();
            $table->foreign('country_code')->references('code')->on('countries')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['display_currency']);
            $table->dropForeign(['country_code']);
            $table->dropIndex('idx_users_type_status');
            $table->dropIndex('idx_users_created');
            $table->dropUnique(['public_id']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'public_id', 'type', 'status', 'phone', 'locale', 'display_currency', 'country_code',
                'two_factor_secret', 'two_factor_confirmed_at', 'two_factor_recovery_codes',
                'last_login_at', 'last_login_ip',
            ]);
        });
    }
};
