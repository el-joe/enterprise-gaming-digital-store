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
        Schema::create('digital_code_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('digital_code_id')->constrained('digital_codes')->restrictOnDelete();
            $table->string('actor_type', 32);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('purpose', 32);
            $table->string('reason', 500)->nullable();
            $table->boolean('granted');
            $table->string('denial_reason', 64)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['digital_code_id', 'created_at'], 'idx_dca_code');
            $table->index(['actor_type', 'actor_id', 'created_at'], 'idx_dca_actor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_code_accesses');
    }
};
