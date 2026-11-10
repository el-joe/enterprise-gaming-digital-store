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
        Schema::create('pricing_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('scope', 32);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('kind', 32);
            $table->char('country_code', 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('fixed_markup', 20, 6)->nullable();
            $table->decimal('percent_markup', 7, 4)->nullable();
            $table->decimal('min_margin_pct', 7, 4)->nullable();
            $table->decimal('min_margin_fixed', 20, 6)->nullable();
            $table->string('below_margin_policy', 32)->nullable();
            $table->json('conditions')->nullable();
            $table->integer('priority')->default(100);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'scope', 'scope_id', 'country_code', 'currency', 'priority'], 'idx_prl_scope');
            $table->foreign('country_code')->references('code')->on('countries')->restrictOnDelete();
            $table->foreign('currency')->references('code')->on('currencies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
