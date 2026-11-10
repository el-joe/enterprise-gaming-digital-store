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
        Schema::create('supplier_balance_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->decimal('balance', 20, 6);
            $table->string('currency', 3);
            $table->timestamp('captured_at');
            $table->timestamp('created_at')->nullable();
            $table->index(['supplier_id', 'captured_at'], 'idx_sbs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_balance_snapshots');
    }
};
