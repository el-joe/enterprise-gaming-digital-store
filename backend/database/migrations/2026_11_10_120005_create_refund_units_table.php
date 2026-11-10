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
        Schema::create('refund_units', function (Blueprint $table): void {
            $table->foreignId('refund_id')->constrained('refunds')->restrictOnDelete();
            $table->foreignId('order_item_unit_id')->constrained('order_item_units')->restrictOnDelete();
            $table->timestamps();
            $table->primary(['refund_id', 'order_item_unit_id']);
            $table->unique(['order_item_unit_id'], 'uq_ru_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_units');
    }
};
