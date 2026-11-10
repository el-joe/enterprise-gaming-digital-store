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
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64);
            $table->string('type', 32);
            $table->decimal('value', 20, 6);
            $table->char('currency', 3)->nullable();
            $table->decimal('max_discount_base', 20, 6)->nullable();
            $table->decimal('min_order_base', 20, 6)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->nullable()->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->json('applicable_to')->nullable();
            $table->boolean('stackable')->default(false);
            $table->boolean('allow_below_margin')->default(false);
            $table->boolean('first_order_only')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by_type', 32);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['code']);
            $table->index(['is_active', 'ends_at'], 'idx_c_active');
            $table->foreign('currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
