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
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('adapter_key', 64);
            $table->string('status', 32)->default('inactive');
            $table->string('mode', 32)->default('sandbox');
            $table->string('base_url_override', 255)->nullable();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->boolean('fallback_enabled')->default(false);
            $table->foreignId('fallback_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->unsignedTinyInteger('max_fallback_attempts')->default(2);
            $table->unsignedInteger('connect_timeout_ms')->default(5000);
            $table->unsignedInteger('request_timeout_ms')->default(30000);
            $table->json('retry_policy')->nullable();
            $table->unsignedInteger('verification_deadline_minutes')->default(120);
            $table->decimal('per_request_cap_base', 20, 6)->nullable();
            $table->decimal('low_balance_threshold', 20, 6)->nullable();
            $table->char('balance_currency', 3)->nullable();
            $table->decimal('last_balance', 20, 6)->nullable();
            $table->timestamp('last_balance_at')->nullable();
            $table->string('health', 32)->default('unknown');
            $table->timestamp('circuit_open_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name'], 'uq_suppliers_name');
            $table->index(['status', 'priority'], 'idx_sup_status');
            $table->foreign('balance_currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
