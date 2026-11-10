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
        Schema::create('supplier_health_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->boolean('reachable');
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('error', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['supplier_id', 'created_at'], 'idx_shc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_health_checks');
    }
};
