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
        Schema::create('automation_outbox', function (Blueprint $table): void {
            $table->id();
            $table->ulid('event_id');
            $table->string('event_type', 96);
            $table->json('payload');
            $table->string('status', 32);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id']);
            $table->index(['status', 'next_attempt_at'], 'idx_ao_pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_outbox');
    }
};
