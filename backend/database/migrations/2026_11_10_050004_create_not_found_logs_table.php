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
        Schema::create('not_found_logs', function (Blueprint $table): void {
            $table->id();
            $table->char('path_hash', 64);
            $table->string('path', 500);
            $table->unsignedInteger('hits')->default(1);
            $table->timestamp('last_seen_at');
            $table->timestamps();
            $table->unique(['path_hash']);
            $table->index(['hits']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('not_found_logs');
    }
};
