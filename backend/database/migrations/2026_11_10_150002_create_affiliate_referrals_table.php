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
        Schema::create('affiliate_referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->restrictOnDelete();
            $table->char('visitor_token_hash', 64);
            $table->string('landing_url', 500)->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('status', 32);
            $table->timestamp('clicked_at');
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->unique(['order_id'], 'uq_ar_order');
            $table->index(['affiliate_id', 'status', 'clicked_at']);
            $table->index(['visitor_token_hash', 'clicked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_referrals');
    }
};
