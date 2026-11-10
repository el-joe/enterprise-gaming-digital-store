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
        Schema::create('supplier_api_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('supplier_transaction_id')->nullable()->constrained('supplier_transactions')->nullOnDelete();
            $table->string('operation', 64);
            $table->string('method', 8);
            $table->string('url', 500);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('succeeded');
            $table->string('error_class', 120)->nullable();
            $table->json('request_redacted')->nullable();
            $table->json('response_redacted')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['supplier_id', 'created_at'], 'idx_sal_supplier');
            $table->index(['supplier_id', 'succeeded', 'created_at'], 'idx_sal_failed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_api_logs');
    }
};
