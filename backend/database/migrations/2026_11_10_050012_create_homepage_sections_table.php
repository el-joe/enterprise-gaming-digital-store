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
        Schema::create('homepage_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 64);
            $table->string('title', 190)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->string('source', 32);
            $table->json('settings')->nullable();
            $table->boolean('is_active');
            $table->integer('sort_order');
            $table->timestamps();
            $table->unique(['key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
