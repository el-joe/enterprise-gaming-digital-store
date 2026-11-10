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
        Schema::create('manual_fulfillment_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_item_unit_id')->constrained('order_item_units')->restrictOnDelete();
            $table->string('status', 32);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_note')->nullable();
            $table->timestamps();
            $table->unique(['order_item_unit_id']);
            $table->index(['status', 'created_at'], 'idx_mft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_fulfillment_tasks');
    }
};
