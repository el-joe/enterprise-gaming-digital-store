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
        Schema::create('currencies', function (Blueprint $table): void {
            $table->char('code', 3)->primary();
            $table->string('name', 80);
            $table->string('symbol', 8);
            $table->unsignedTinyInteger('exponent');
            $table->json('rounding_rule')->nullable();
            $table->decimal('markup_pct', 7, 4)->default('0');
            $table->boolean('is_active');
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'sort_order'], 'idx_cur_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
