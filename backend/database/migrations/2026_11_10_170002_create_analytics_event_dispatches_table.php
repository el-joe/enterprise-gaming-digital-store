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
        Schema::create('analytics_event_dispatches', function (Blueprint $table): void {
            $table->id();
            $table->string('event_name', 32);
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->string('dedupe_key', 128);
            $table->json('payload');
            $table->string('status', 32);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['dedupe_key'], 'uq_aed');
            $table->index(['status', 'created_at'], 'idx_aed_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_event_dispatches');
    }
};
