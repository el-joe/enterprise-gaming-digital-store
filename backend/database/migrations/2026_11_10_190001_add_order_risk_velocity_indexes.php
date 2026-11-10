<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the risk engine's velocity signals (M7): orders per IP and payments per card
 * fingerprint within a time window.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['ip_address', 'created_at'], 'idx_o_ip');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['card_fingerprint_hash', 'created_at'], 'idx_pay_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('idx_pay_fingerprint');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('idx_o_ip');
        });
    }
};
