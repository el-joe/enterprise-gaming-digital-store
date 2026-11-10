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
        Schema::create('customer_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('risk_level', 32)->default('low');
            $table->text('risk_notes')->nullable();
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('lifetime_value_base', 20, 6)->default('0');
            $table->timestamp('first_order_at')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id']);
            $table->index(['risk_level'], 'idx_cp_risk');
            $table->index(['last_order_at'], 'idx_cp_last_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
