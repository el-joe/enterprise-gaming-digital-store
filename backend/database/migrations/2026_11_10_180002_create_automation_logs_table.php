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
        Schema::create('automation_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('workflow_id', 16);
            $table->string('execution_key', 128);
            $table->string('status', 32);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->json('summary')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->unique(['workflow_id', 'execution_key'], 'uq_alog');
            $table->index(['workflow_id', 'started_at'], 'idx_alog');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
    }
};
