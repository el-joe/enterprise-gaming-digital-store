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
        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('source_path', 500);
            $table->char('source_hash', 64);
            $table->string('target_path', 500)->nullable();
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('hits')->default(0);
            $table->boolean('is_active');
            $table->timestamps();
            $table->unique(['source_hash'], 'uq_red_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
