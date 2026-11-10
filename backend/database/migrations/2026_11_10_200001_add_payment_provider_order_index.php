<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M10: Paymob callbacks name the Paymob order (`obj.order.id`, stored as
 * `payments.provider_order_id`) before our side knows the transaction id, so webhook processing
 * locates the attempt by it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['payment_gateway_id', 'provider_order_id'], 'idx_pay_provider_order');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('idx_pay_provider_order');
        });
    }
};
