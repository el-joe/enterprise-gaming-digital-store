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
        Schema::create('supplier_sync_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('triggered_by', 64);
            $table->string('status', 32);
            $table->unsignedInteger('items_seen')->default(0);
            $table->unsignedInteger('items_created')->default(0);
            $table->unsignedInteger('items_updated')->default(0);
            $table->unsignedInteger('items_failed')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('window_key', 64)->nullable();
            $table->timestamps();
            $table->unique(['supplier_id', 'type', 'window_key'], 'uq_ssr_window');
            $table->index(['supplier_id', 'type', 'started_at'], 'idx_ssr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sync_runs');
    }
};
