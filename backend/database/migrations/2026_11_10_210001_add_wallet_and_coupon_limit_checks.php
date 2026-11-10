<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * M11: database-level backstops for the two limits the services enforce under row locks. A wallet
 * balance can never go below zero, and a coupon can never be used more often than its global
 * limit, even if a future code path forgot the lock (MySQL 8.0.16+ enforces CHECK constraints).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE customer_wallets ADD CONSTRAINT chk_cw_balance_nonneg CHECK (balance >= 0)');
        DB::statement('ALTER TABLE coupons ADD CONSTRAINT chk_c_usage CHECK (usage_limit IS NULL OR used_count <= usage_limit)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE coupons DROP CHECK chk_c_usage');
        DB::statement('ALTER TABLE customer_wallets DROP CHECK chk_cw_balance_nonneg');
    }
};
