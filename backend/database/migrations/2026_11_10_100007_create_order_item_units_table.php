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
        Schema::create('order_item_units', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete();
            $table->string('status', 32);
            $table->json('selection_snapshot')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('first_revealed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->unique(['public_id']);
            $table->index(['order_id', 'status'], 'idx_oiu_order');
            $table->index(['status', 'updated_at'], 'idx_oiu_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_units');
    }
};
