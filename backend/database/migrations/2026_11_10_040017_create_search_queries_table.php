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
        Schema::create('search_queries', function (Blueprint $table): void {
            $table->id();
            $table->string('term', 190);
            $table->string('locale', 8);
            $table->unsignedInteger('hits')->default(1);
            $table->unsignedInteger('results_count')->default(0);
            $table->timestamp('last_searched_at');
            $table->timestamps();
            $table->unique(['term', 'locale'], 'uq_sq');
            $table->index(['locale', 'hits'], 'idx_sq_popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
