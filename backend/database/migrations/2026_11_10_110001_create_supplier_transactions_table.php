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
        Schema::create('supplier_transactions', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->foreignId('order_item_unit_id')->constrained('order_item_units')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('supplier_product_mapping_id')->nullable()->constrained('supplier_product_mappings')->nullOnDelete();
            $table->unsignedTinyInteger('attempt_no');
            $table->string('client_reference', 100);
            $table->string('external_transaction_id', 128)->nullable();
            $table->string('status', 32);
            $table->string('outcome_reason', 128)->nullable();
            $table->string('supplier_error_code', 64)->nullable();
            $table->boolean('is_fallback')->default(false);
            $table->string('code_retrieval_status', 32)->default('not_applicable');
            $table->decimal('requested_amount', 20, 6);
            $table->string('requested_currency', 3);
            $table->decimal('actual_cost', 20, 6)->nullable();
            $table->string('actual_cost_currency', 3)->nullable();
            $table->boolean('cost_reversal_pending')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('next_verification_at')->nullable();
            $table->unsignedSmallInteger('verification_attempts')->default(0);
            $table->timestamp('verification_deadline_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->unsignedBigInteger('active_unit_key')->nullable()->storedAs(
                "CASE WHEN status IN ('submitting','accepted','pending_verification','succeeded') THEN order_item_unit_id END"
            );
            $table->timestamps();
            $table->unique(['public_id']);
            $table->unique(['active_unit_key'], 'uq_st_active_unit');
            $table->unique(['supplier_id', 'client_reference', 'attempt_no'], 'uq_st_reference');
            $table->unique(['supplier_id', 'external_transaction_id'], 'uq_st_external');
            $table->index(['status', 'next_verification_at'], 'idx_st_verify');
            $table->index(['status', 'submitted_at'], 'idx_st_stale');
            $table->index(['order_item_unit_id', 'attempt_no'], 'idx_st_unit');
            $table->index(['supplier_id', 'submitted_at'], 'idx_st_supplier_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_transactions');
    }
};
