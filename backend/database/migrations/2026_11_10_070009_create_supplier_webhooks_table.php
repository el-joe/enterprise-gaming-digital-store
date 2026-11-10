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
        Schema::create('supplier_webhooks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('provider_event_id', 128);
            $table->boolean('signature_valid')->nullable();
            $table->text('payload');
            $table->string('status', 32);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['supplier_id', 'provider_event_id'], 'uq_sw');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_webhooks');
    }
};
