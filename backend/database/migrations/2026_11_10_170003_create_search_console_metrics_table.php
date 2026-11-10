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
        Schema::create('search_console_metrics', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('dimension', 32);
            $table->char('key_hash', 64);
            $table->string('key_value', 1000);
            $table->unsignedInteger('clicks');
            $table->unsignedInteger('impressions');
            $table->decimal('ctr', 7, 6);
            $table->decimal('position', 7, 3);
            $table->timestamps();
            $table->unique(['date', 'dimension', 'key_hash'], 'uq_scm');
            $table->index(['dimension', 'date', 'clicks']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_console_metrics');
    }
};
